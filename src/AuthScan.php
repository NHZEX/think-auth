<?php
declare(strict_types=1);

namespace Zxin\Think\Auth;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use think\App;
use Zxin\Think\Annotation\DumpValue;
use Zxin\Think\Annotation\Scanning;
use Zxin\Think\Auth\Annotation\Auth;
use Zxin\Think\Auth\Annotation\AuthMeta;
use Zxin\Think\Auth\Annotation\Base;
use Zxin\Think\Auth\Exception\AuthException;
use function array_merge;
use function is_array;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;

class AuthScan
{
    use InteractsWithStorage;

    const ROOT_NODE = '__ROOT__';

    /**
     * @var App
     */
    protected $app;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Permission
     */
    protected $permission;

    protected $permissions = [];
    protected $nodes       = [];
    protected $controllers = [];

    protected $debug = false;

    /**
     * AuthScan constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->reader = new AnnotationReader();

        $this->permission = Permission::getInstance();
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function refresh()
    {
        $this->scanAnnotation();

        $output = $this->build();
        $this->export($output);
    }

    public function export($value)
    {
        $dump = new DumpValue(Permission::getDumpFilePath());
        $dump->load();
        $dump->save($value);
    }

    public function loadDefaultPermissions()
    {
        $default           = App::getInstance()->config->get('auth.permissions', []);
        $this->permissions = array_merge($default, $this->permissions);
    }

    protected function scanAnnotation()
    {
        $this->permissions = [];
        $this->nodes       = [];
        $this->controllers = [];

        $this->loadDefaultPermissions();

        $scanning = new Scanning($this->app);

        foreach ($scanning->scanningClass() as $class) {
            try {
                $refClass = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new AuthException('load class fail: ' . $class, 0, $e);
            }
            if ($refClass->isAbstract() || $refClass->isTrait()) {
                continue;
            }

            $namespaces      = $scanning->getControllerNamespaces();
            $controllerLayer = $scanning->getControllerLayer();
            // 是否多应用
            $isApp = (0 !== strpos($class, $namespaces . $controllerLayer));

            if ($isApp) {
                $controllerUrl = substr($class, strlen($namespaces));
                $appPos        = strpos($controllerUrl, '\\');
                $appName       = substr($controllerUrl, 0, $appPos);
                $controllerUrl = substr($controllerUrl, $appPos + strlen($controllerLayer . '\\') + 1);
                $controllerUrl = $appName . '/' . strtolower(str_replace('\\', '.', $controllerUrl));
            } else {
                $controllerUrl = substr($class, strlen($namespaces . $controllerLayer . '\\'));
                $controllerUrl = strtolower(str_replace('\\', '.', $controllerUrl));
            }


            foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
                if ($refMethod->isStatic()) {
                    continue;
                }
                $methodName = $refMethod->getName();
                if (0 === strpos($methodName, '_')) {
                    continue;
                }

                $nodeUrl    = $controllerUrl . '/' . strtolower($methodName);
                $methodPath = $class . '::' . $methodName;

                if (PHP_VERSION_ID >= 80000) {
                    foreach ($refMethod->getAttributes(Base::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                        $this->handleAttributes($attribute->newInstance(), $methodPath, $nodeUrl, $controllerUrl, $methodName);
                    }
                }

                $annotations = $this->reader->getMethodAnnotations($refMethod);
                foreach ($annotations as $auth) {
                    if ($auth instanceof Base) {
                        $this->handleAttributes($auth, $methodPath, $nodeUrl, $controllerUrl, $methodName);
                    }
                }

                $this->controllers[$class][$methodName] = $nodeUrl;
            }
        }
    }

    /**
     * @param Base   $obj
     * @param string $methodPath
     * @param string $nodeUrl
     * @param string $controllerUrl
     * @param string $methodName
     * @return void
     */
    protected function handleAttributes(Base $obj, string $methodPath, string $nodeUrl, string $controllerUrl, string $methodName)
    {
        if ($obj instanceof Auth) {
            $this->handleAuth($obj, $methodPath, $nodeUrl, $controllerUrl, $methodName);
        } elseif ($obj instanceof AuthMeta) {
            $this->handleAuthMeta($obj, $methodPath, $nodeUrl);
        }
    }

    protected function handleAuth(Auth $auth, string $methodPath, string $nodeUrl, string $controllerUrl, string $methodName): void
    {
        if (empty($auth->name)) {
            throw new AuthException('annotation value not empty(Auth): ' . $methodPath);
        }
        $authStr  = $this->parseAuth($auth->name, $controllerUrl, $methodName);
        $features = "node@{$nodeUrl}";
        if (isset($this->permissions[$authStr]['allow']) && !is_array($this->permissions[$authStr]['allow'])) {
            $this->permissions[$authStr]['allow'] = [];
        }
        $this->permissions[$authStr]['allow'][] = $features;
        // 记录节点控制信息
        $this->nodes[$features] = [
            'class'  => $methodPath,
            'policy' => $auth->policy,
            'desc'   => '',
        ];

        if ($this->debug) {
            echo sprintf(
                '> %s%s%s  => %s%s',
                $methodPath,
                $this->nodes[$features]['desc'] ? ": {$this->nodes[$features]['desc']}" : '',
                PHP_EOL,
                $features,
                PHP_EOL
            );
        }
    }

    /**
     * @param AuthMeta $auth
     * @param string   $methodPath
     * @param string   $nodeUrl
     * @return void
     */
    protected function handleAuthMeta(AuthMeta $auth, string $methodPath, string $nodeUrl): void
    {
        if (empty($auth->desc)) {
            throw new AuthException('annotation value not empty(AuthDescription): ' . $methodPath);
        }
        $features = "node@{$nodeUrl}";
        if (isset($this->nodes[$features])) {
            $this->nodes[$features]['desc']   = $auth->desc;
            $this->nodes[$features]['policy'] = $auth->policy;
        } else {
            throw new AuthException('nodes not ready(AuthDescription): ' . $methodPath);
        }
    }

    protected function parseAuth($auth, $controllerUrl, $methodName): string
    {
        if ('self' === $auth) {
            return str_replace('/', '.', $controllerUrl) . '.' . strtolower($methodName);
        }
        return $auth;
    }
}
