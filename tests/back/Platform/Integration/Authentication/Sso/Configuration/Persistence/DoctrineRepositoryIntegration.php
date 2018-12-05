<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Configuration\Persistence;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Root;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Akeneo\Test\Integration\TestCase;

class DoctrineRepositoryIntegration extends TestCase
{
    public function testSaveAndFindANewConfiguration()
    {
        $configRepository = $this->get('akeneo_authentication.sso.configuration.repository');

        $config = new Root(
            Code::fromString('sso'),
            new IdentityProvider(
                EntityId::fromString('https://idp.jambon.com'),
                Url::fromString('https://idp.jambon.com/'),
                Certificate::fromString('public_certificate')
            ),
            new ServiceProvider(
                EntityId::fromString('https://sp.jambon.com'),
                Certificate::fromString('public_certificate'),
                Certificate::fromString('private_certificate')
            )
        );

        $configRepository->save($config);
        $retrievedConfig = $configRepository->find('sso');

        $this->assertEquals($config, $retrievedConfig);
    }

    public function testUpdateAnExistingConfiguration()
    {
        $configRepository = $this->get('akeneo_authentication.sso.configuration.repository');

        $config = new Root(
            Code::fromString('sso'),
            new IdentityProvider(
                EntityId::fromString('https://idp.jambon.com'),
                Url::fromString('https://idp.jambon.com/'),
                Certificate::fromString('public_certificate')
            ),
            new ServiceProvider(
                EntityId::fromString('https://sp.jambon.com'),
                Certificate::fromString('public_certificate'),
                Certificate::fromString('private_certificate')
            )
        );
        $configRepository->save($config);

        $newConfig = new Root(
            Code::fromString('sso'),
            new IdentityProvider(
                EntityId::fromString('https://idp.jambon-sso.com'),
                Url::fromString('https://idp.jambon-sso.com/'),
                Certificate::fromString('public_certificate')
            ),
            new ServiceProvider(
                EntityId::fromString('https://sp.jambon-sso.com'),
                Certificate::fromString('public_certificate'),
                Certificate::fromString('private_certificate')
            )
        );
        $configRepository->save($newConfig);
        $retrievedConfig = $configRepository->find('sso');

        $this->assertEquals($newConfig, $retrievedConfig);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return null;
    }
}
