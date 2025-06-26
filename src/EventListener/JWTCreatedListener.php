<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        $payload = $event->getData();

        $payload['user_id'] = $user->getId();
        $payload['email'] = $user->getEmail();
        $payload['username'] = $user->getAlias();
        $payload['roles'] = $user->getRoles();

        $event->setData($payload);
    }
}
