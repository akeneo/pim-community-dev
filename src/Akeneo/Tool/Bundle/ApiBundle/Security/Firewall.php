<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\FirewallMapInterface;

final class Firewall
{
    private const API_FIREWALL = 'api';

    public function __construct(
        private FirewallMapInterface $firewall,
        private RequestStack $requestStack
    ) {
    }

    public function isCurrentRequestInsideTheApiFirewall(): bool
    {
        return self::API_FIREWALL === $this->getName();
    }

    private function getName(): ?string
    {
        // The method getFirewallConfig is only part of Symfony\Bundle\SecurityBundle\Security\FirewallMap,
        // not in the FirewallMapInterface.
        // In EE, we override the "@security.firewall.map" service with another class that is not extending
        // Symfony\Bundle\SecurityBundle\Security\FirewallMap but still provide getFirewallConfig.
        if ($this->firewall instanceof FirewallMap || method_exists($this->firewall, 'getFirewallConfig')) {
            $firewallConfig = $this->firewall->getFirewallConfig($this->requestStack->getCurrentRequest());

            if ($firewallConfig instanceof FirewallConfig) {
                return $firewallConfig->getName();
            }
        }

        return null;
    }
}
