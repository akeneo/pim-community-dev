<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
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

    public function __construct(FirewallMapInterface $firewallMap, Repository $configRepository)
    {
        $this->firewallMap = $firewallMap;
        $this->configRepository = $configRepository;
    }

    public function getListeners(Request $request)
    {
        $context = $this->getFirewallContext($request);

        if (null === $context) {
            return array(array(), null);
        }

        return array($context->getListeners(), $context->getExceptionListener());
    }

    private function getFirewallContext(Request $request)
    {
        if ($request->attributes->has('_firewall_context')) {
            $storedContextId = $request->attributes->get('_firewall_context');
            foreach ($this->firewallMap->map as $contextId => $requestMatcher) {
                if ($contextId === $storedContextId) {
                    return $this->firewallMap->container->get($contextId);
                }
            }

            $request->attributes->remove('_firewall_context');
        }

        foreach ($this->firewallMap->map as $contextId => $requestMatcher) {
            if (null === $requestMatcher || $requestMatcher->matches($request)) {
                $request->attributes->set('_firewall_context', $contextId);

                $contextService = $this->firewallMap->container->get($contextId);
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

        return $config->isEnabled()->toBoolean();
    }
}
