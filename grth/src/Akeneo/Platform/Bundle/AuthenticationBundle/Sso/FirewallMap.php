<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallContext;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap as SymfonyFirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\FirewallMapInterface;

class FirewallMap extends SymfonyFirewallMap
{
    private const CONFIGURATION_CODE = 'authentication_sso';
    private const SSO_FIREWALL_NAME = 'sso';

    private FirewallMapInterface $firewallMap;
    private Repository $configRepository;
    private ContainerInterface $container;
    private iterable $map;

    public function __construct(ContainerInterface $container, iterable $map, Repository $configRepository)
    {
        $this->container = $container;
        $this->map = $map;
        $this->contexts = new \SplObjectStorage();
        $this->configRepository = $configRepository;
    }

    public function getListeners(Request $request): array
    {
        $context = $this->getFirewallContext($request);

        if (null === $context) {
            return [[], null];
        }

        return [$context->getListeners(), $context->getExceptionListener(), $context->getLogoutListener()];
    }

    public function getFirewallConfig(Request $request): ?FirewallConfig
    {
        $context = $this->getFirewallContext($request);

        if (null === $context) {
            return null;
        }

        return $context->getConfig();
    }

    private function getFirewallContext(Request $request): ?FirewallContext
    {
        if ($request->attributes->has('_firewall_context')) {
            $storedContextId = $request->attributes->get('_firewall_context');
            foreach ($this->map as $contextId => $requestMatcher) {
                if ($contextId === $storedContextId) {
                    return $this->container->get($contextId);
                }
            }

            $request->attributes->remove('_firewall_context');
        }

        foreach ($this->map as $contextId => $requestMatcher) {
            if (null === $requestMatcher || $requestMatcher->matches($request)) {
                $request->attributes->set('_firewall_context', $contextId);

                $contextService = $this->container->get($contextId);
                if (self::SSO_FIREWALL_NAME === $contextService->getConfig()->getName()
                    && !$this->isSSOEnabled()
                ) {
                    continue;
                }

                return $contextService;
            }
        }

        return null;
    }

    private function isSSOEnabled(): bool
    {
        try {
            $config = $this->configRepository->find(self::CONFIGURATION_CODE);
        } catch (ConfigurationNotFound $e) {
            return false;
        }

        return $config->isEnabled();
    }
}
