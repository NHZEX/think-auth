<?php
declare(strict_types=1);

namespace Zxin\Think\Auth;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use think\App;

class AuthScan
{
    use InteractsWithScanning;
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
}
