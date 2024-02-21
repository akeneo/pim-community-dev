<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Akeneo\Tool\Bundle\ApiBundle\Security\Firewall;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiRequestLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Firewall $firewall,
        private TokenStorageInterface $tokenStorage,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        try {
            if (!$event->isMainRequest()) {
                return;
            }

            $request = $event->getRequest();

            if (!$this->firewall->isCurrentRequestInsideTheApiFirewall()) {
                return;
            }

            $this->logger->info('request', [
                'method' => $request->getMethod(),
                'path_info' => $this->getCurrentUrl($request),
                'user' => $this->getCurrentUsername(),
            ]);
        } catch (\Exception $e) {
        }
    }

    private function getCurrentUsername(): ?string
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return null;
        }

        return $token->getUserIdentifier();
    }

    private function getCurrentUrl(Request $request): string
    {
        return sprintf('%s%s', $request->getSchemeAndHttpHost(), $request->getPathInfo());
    }
}
