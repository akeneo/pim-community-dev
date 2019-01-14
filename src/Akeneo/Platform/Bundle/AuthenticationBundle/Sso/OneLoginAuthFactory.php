<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use OneLogin\Saml2\Auth;

/**
 * Authentication service factory used to override static SAML configuration with our dynamic configuration
 * defined by the PIM administrator.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class OneLoginAuthFactory
{
    private const CONFIGURATION_CODE = 'authentication_sso';

    /** @var Repository */
    private $configRepository;

    /** @var array */
    private $originalConfig;

    public function __construct(
        Repository $configRepository,
        array $originalConfig
    ) {
        $this->configRepository = $configRepository;
        $this->originalConfig = $originalConfig;
    }

    public function create(): Auth
    {
        $config = $this->overrideOriginalConfig($this->originalConfig);

        return new Auth($config);
    }

    private function overrideOriginalConfig(array $originalConfig): array
    {
        try {
            $storedConfig = $this->configRepository->find(self::CONFIGURATION_CODE);
        } catch (ConfigurationNotFound $e) {
            return $originalConfig;
        }

        $normalizedStoredConfig = $storedConfig->toArray();

        $config = $originalConfig;
        $config['idp']['entityId']                   = $normalizedStoredConfig['identityProvider']['entityId'];
        $config['idp']['singleSignOnService']['url'] = $normalizedStoredConfig['identityProvider']['signOnUrl'];
        $config['idp']['singleLogoutService']['url'] = $normalizedStoredConfig['identityProvider']['logoutUrl'];
        $config['idp']['x509cert']                   = $normalizedStoredConfig['identityProvider']['certificate'];
        $config['sp']['entityId']                    = $normalizedStoredConfig['serviceProvider']['entityId'];
        $config['sp']['x509cert']                    = $normalizedStoredConfig['serviceProvider']['certificate'];
        $config['sp']['privateKey']                  = $normalizedStoredConfig['serviceProvider']['privateKey'];

        return $config;
    }
}
