<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\FirewallMapInterface;

final class ValidateApiRequestQueryParametersSubscriber implements EventSubscriberInterface
{
    private const API_FIREWALL = 'api';

    public function __construct(
        private FirewallMapInterface $firewall
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->isCurrentRequestInsideTheApiFirewall($request)) {
            return;
        }

        $query = $request->query;

        if ($query->count() === 0) {
            return;
        }

        foreach ($query->all() as $parameter) {
            if (is_array($parameter)) {
                throw new BadRequestHttpException('Bracket syntax is not supported in query parameters.');
            }
        }
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
