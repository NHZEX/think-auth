<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Middleware;

use Zxin\Think\Auth\ParseAuthorization;
use Closure;
use think\App;
use think\Request;
use think\Response;
use think\Session;

class SessionInit
{
    /** @var App */
    protected $app;

    /** @var Session */
    protected $session;

    public function __construct(App $app, Session $session)
    {
        $this->app     = $app;
        $this->session = $session;
    }

    /**
     * Session初始化
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Session初始化
        $varSessionId = $this->app->config->get('session.var_session_id');
        $headerSessionId = $this->app->config->get('session.var_header', 'X-TOKEN');
        $cookieName   = $this->session->getName();

        /** @var ParseAuthorization $token */
        $token = $this->app->make(ParseAuthorization::class);

        if ($xToken = $token->getToken()) {
            $sessionId = $xToken;
        } elseif ($xToken = $request->header($headerSessionId)) {
            $sessionId = $xToken;
        } elseif ($varSessionId && $request->request($varSessionId)) {
            $sessionId = $request->request($varSessionId);
        } else {
            $sessionId = $request->cookie($cookieName);
        }

        if ($sessionId) {
            $this->session->setId($sessionId);
        }

        $this->session->init();

        $request->withSession($this->session);

        /** @var Response $response */
        $response = $next($request);

        $response->setSession($this->session);

        // 不需要设置cookie
        // $this->app->cookie->set(
        //     $cookieName,
        //     $this->session->getId(),
        //     $this->app->config->get('session.cookie', [])
        // );

        return $response;
    }

    /**
     * @param Response $response
     */
    public function end(Response $response)
    {
        $this->session->save();
    }
}
