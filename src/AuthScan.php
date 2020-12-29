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
use Zxin\Think\Auth\Annotation\AuthNode;
use Zxin\Think\Auth\Exception\AuthException;

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
    protected $nodes = [];
    protected $controllers = [];

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

    public function refresh()
    {
        $this->scanAnnotation();

        $output = $this->build();

        $dump = new DumpValue(app_path() . 'auth_storage.php');
        $dump->load();
        $dump->save($output);
    }

    public function loadDefaultPermissions()
    {
        $default = App::getInstance()->config->get('auth.permissions', []);
        $this->permissions = array_merge($default, $this->permissions);
    }

    protected function scanAnnotation()
    {
        $this->permissions = [];
        $this->nodes = [];
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

            $namespaces = $scanning->getControllerNamespaces();
            $controllerLayer = $scanning->getControllerLayer();
            // 是否多应用
            $isApp = (0 !== strpos($class, $namespaces . $controllerLayer));

            if ($isApp) {
                $controllerUrl = substr($class, strlen($namespaces));
                $appPos = strpos($controllerUrl, '\\');
                $appName = substr($controllerUrl, 0, $appPos);
                $controllerUrl = substr($controllerUrl, $appPos + strlen($controllerLayer . '\\') + 1);
                $controllerUrl = $appName. '/' . strtolower(str_replace('\\', '.', $controllerUrl));
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

                $nodeUrl = $controllerUrl . '/' . strtolower($methodName);
                $methodPath = $class . '::' . $methodName;
                $annotations = $this->reader->getMethodAnnotations($refMethod);
                foreach ($annotations as $auth) {
                    if ($auth instanceof Auth) {
                        if (empty($auth->value)) {
                            throw new AuthException('annotation value not empty(Auth): ' . $methodPath);
                        }
                        $authStr = $this->parseAuth($auth->value, $controllerUrl, $methodName);
                        $features = "node@{$nodeUrl}";
                        $this->permissions[$authStr][$methodPath] = $features;
                        // 记录节点控制信息
                        $this->nodes[$features] = [
                            'class'  => $methodPath,
                            'policy' => $auth->policy,
                            'desc'   => '',
                        ];
                    } elseif ($auth instanceof AuthNode) {
                        if (empty($auth->value)) {
                            throw new AuthException('annotation value not empty(AuthDescription): ' . $methodPath);
                        }
                        $features = "node@{$nodeUrl}";
                        if (isset($this->nodes[$features])) {
                            $this->nodes[$features]['desc'] = $auth->value;
                            $this->nodes[$features]['policy'] = $auth->policy;
                        } else {
                            throw new AuthException('nodes not ready(AuthDescription): ' . $methodPath);
                        }
                    }
                }

                $this->controllers[$class][$methodName] = $nodeUrl;
            }
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
