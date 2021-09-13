<?php


namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Configuration;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\DeactivateCommand;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Assert\Assert;

class DeactivateCommandIntegration extends TestCase
{
    public function testItDeactivatesConfiguration()
    {
        $config = new Configuration(
            new Code(Configuration::DEFAULT_CODE),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.example.com'),
                new Url('https://idp.example.com/login'),
                new Url('https://idp.example.com/logout'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.example.com'),
                new Certificate('public_certificate'),
                new Certificate('private_key')
            )
        );

        $this->get('akeneo_authentication.sso.configuration.repository')->save($config);

        $this->get(DeactivateCommand::class)->run(new ArrayInput([]), new BufferedOutput());

        $deactivatedConfig = $this->get('akeneo_authentication.sso.configuration.repository')->find(Configuration::DEFAULT_CODE);

        Assert::isInstanceOf($deactivatedConfig, Configuration::class);
        Assert::false($deactivatedConfig->isEnabled());
    }

    public function testItDoesNothingWhenThereIsNoConfiguration()
    {
        $this->get(DeactivateCommand::class)->run(new ArrayInput([]), new BufferedOutput());

        $this->expectException(ConfigurationNotFound::class);
        $this->get('akeneo_authentication.sso.configuration.repository')->find(Configuration::DEFAULT_CODE);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
