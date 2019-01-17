<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Configuration\Persistence\Sql;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Akeneo\Test\Integration\TestCase;

class SqlRepositoryIntegration extends TestCase
{
    public function testSaveAndFindANewConfiguration()
    {
        $configRepository = $this->get('akeneo_authentication.sso.configuration.repository');

        $config = new Configuration(
            new Code('sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/login'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('public_certificate'),
                new Certificate('private_key')
            )
        );

        $configRepository->save($config);
        $retrievedConfig = $configRepository->find('sso');

        $this->assertEquals($config, $retrievedConfig);
    }

    public function testUpdateAnExistingConfiguration()
    {
        $configRepository = $this->get('akeneo_authentication.sso.configuration.repository');

        $config = new Configuration(
            new Code('sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/login'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('public_certificate'),
                new Certificate('private_key')
            )
        );
        $configRepository->save($config);

        $newConfig = new Configuration(
            new Code('sso'),
            new IsEnabled(false),
            new IdentityProvider(
                new EntityId('https://idp.jambon-sso.com'),
                new Url('https://idp.jambon-sso.com/login'),
                new Url('https://idp.jambon-sso.com/logout'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon-sso.com'),
                new Certificate('public_certificate'),
                new Certificate('private_key')
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
