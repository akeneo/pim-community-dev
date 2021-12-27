<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;

class ApiRequestLogSubscriber implements EventSubscriberInterface
{
    private const API_FIREWALL = 'api';

    public function __construct(
        private FirewallMapInterface $firewall,
        private TokenStorageInterface $tokenStorage,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents()
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

            if (!$this->isCurrentRequestInsideTheApiFirewall($request)) {
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

    private function isCurrentRequestInsideTheApiFirewall(Request $request): bool
    {
        return self::API_FIREWALL === $this->getFirewallName($request);
    }

    private function getFirewallName(Request $request): ?string
    {
        // The method getFirewallConfig is only part of Symfony\Bundle\SecurityBundle\Security\FirewallMap,
        // not in the FirewallMapInterface.
        // In EE, we override the "@security.firewall.map" service with another class that is not extending
        // Symfony\Bundle\SecurityBundle\Security\FirewallMap but still provide getFirewallConfig.
        if ($this->firewall instanceof FirewallMap || method_exists($this->firewall, 'getFirewallConfig')) {
            $firewallConfig = $this->firewall->getFirewallConfig($request);

            if ($firewallConfig instanceof FirewallConfig) {
                return $firewallConfig->getName();
            }
        }

        return null;
    }
}
