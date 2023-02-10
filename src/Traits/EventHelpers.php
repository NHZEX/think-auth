<?php

declare(strict_types=1);

namespace Zxin\Think\Auth\Traits;

use think\Container;
use think\Event;
use Zxin\Think\Auth\Listens\AuthenticatedEvent;
use Zxin\Think\Auth\Listens\LoginEvent;

/**
 * Trait EventHelpers
 * @package Zxin\Think\Auth\Traits
 * @property Container $container
 */
trait EventHelpers
{
    protected function triggerAuthenticatedEvent($user)
    {
        /** @var Event $event */
        $event = $this->container->get('event');
        $event->trigger(AuthenticatedEvent::class, [$user]);
    }

    protected function triggerLoginEvent($user, $remember = false)
    {
        /** @var Event $event */
        $event = $this->container->get('event');
        $event->trigger(LoginEvent::class, [$user]);
    }
}
