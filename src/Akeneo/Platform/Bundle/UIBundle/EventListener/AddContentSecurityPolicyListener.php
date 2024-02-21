<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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
    private ContentSecurityPolicyProvider $contentSecurityPolicyProvider;

    public function __construct(ContentSecurityPolicyProvider $contentSecurityPolicyProvider)
    {
        $this->contentSecurityPolicyProvider = $contentSecurityPolicyProvider;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'addCspHeaders',
        ];
    }

    public function addCspHeaders(ResponseEvent $event): void
    {
        $policy = $this->contentSecurityPolicyProvider->getPolicy();

        $response = $event->getResponse();
        $response->headers->set('Content-Security-Policy', $policy);
        $response->headers->set('X-Content-Security-Policy', $policy);
        $response->headers->set('X-WebKit-CSP', $policy);
    }
}
