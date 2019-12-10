<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Inject CSP headers in response object
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddContentSecurityPolicyListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'addCspHeaders',
        ];
    }

    public function addCspHeaders(FilterResponseEvent $event): void
    {
        $policy = "default-src 'self' *.akeneo.com; script-src 'self' 'unsafe-eval'; img-src 'self' data:";

        $response = $event->getResponse();
        $response->headers->set('Content-Security-Policy', $policy);
        $response->headers->set('X-Content-Security-Policy', $policy);
        $response->headers->set('X-WebKit-CSP', $policy);
    }
}
