<?php

namespace Pim\Bundle\UserBundle\Security;

use Escape\WSSEAuthenticationBundle\Security\Http\Firewall\Listener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class WsseAuthListener
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WsseAuthListener extends Listener
{
    /**
     * Check for a possible CSRF attack in REST API
     *
     * {@inheritdoc}
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
