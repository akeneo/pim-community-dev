<?php

namespace Oro\Bundle\UserBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Escape\WSSEAuthenticationBundle\Security\Http\Firewall\Listener;

class WsseAuthListener extends Listener
{
    /**
     * Check for a possible CSRF attack in REST API
     *
     * @param  GetResponseEvent        $event
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // check for a special "anti-CSRF" header in AJAX calls
        if (!$request->headers->has('X-WSSE')
            && !$request->headers->has('X-CSRF-Header')
        ) {
            throw new AuthenticationException('Possible CSRF attack detected');
        }

        return parent::handle($event);
    }
}
