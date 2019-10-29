<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\FirewallMapInterface;

class FirewallMap implements FirewallMapInterface
{
    private const CONFIGURATION_CODE = 'authentication_sso';
    private const SSO_FIREWALL_NAME = 'sso';

    /** @var FirewallMapInterface */
    private $firewallMap;

    /** @var Repository */
    private $configRepository;

    private $container;
    private $map;

    public function __construct(ContainerInterface $container, iterable $map, Repository $configRepository)
    {
        $this->container = $container;
        $this->map = $map;
        $this->contexts = new \SplObjectStorage();
        $this->configRepository = $configRepository;
    }

    public function getListeners(Request $request)
    {
        $context = $this->getFirewallContext($request);

        if (null === $context) {
            return [[], null];
        }

        return [$context->getListeners(), $context->getExceptionListener(), $context->getLogoutListener()];
    }

    private function getFirewallContext(Request $request)
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
