<?php

namespace Zxin\Think\Auth\Record;

use RuntimeException;
use think\App;
use think\event\HttpEnd;
use think\Response;
use think\Service;
use Zxin\Think\Auth\AuthContext;

class RecordService extends Service
{
    protected $config = [
        'adapter' => null,
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->config = array_merge(
            $this->config,
            $app->config->get('auth.record', [])
        );
    }

    public function register(): void
    {
        $adapter = $this->config['adapter'];
        if (empty($adapter) || !class_exists($adapter)) {
            return;
        }

        $this->listen();

        $this->app->bind(RecordAdapterInterface::class, function () {
            $adapter = new $this->config['adapter']();
            if (!$adapter instanceof RecordAdapterInterface) {
                throw new RuntimeException('record adapter interface invalid');
            }
            return $adapter;
        });
    }

    /**
     * @return RecordAdapterInterface
     */
    protected function getRecordAdapter(): RecordAdapterInterface
    {
        return $this->app->get(RecordAdapterInterface::class);
    }

    protected function listen()
    {
        $this->app->event->listen(HttpEnd::class, function (Response $response) {
            $request = $this->app->request;

            $adapter = $this->getRecordAdapter();
            if (!$adapter->isActivity($request, $response)) {
                return;
            }

            $authCtx = AuthContext::get();
            $accessCtx = RecordHelper::accessLog();
            $adapter->writeRecord($request, $response, $accessCtx, $authCtx);
        });
    }
}
