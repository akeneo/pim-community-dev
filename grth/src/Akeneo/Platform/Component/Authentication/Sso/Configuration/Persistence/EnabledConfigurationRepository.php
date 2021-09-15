<?php
declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;

final class EnabledConfigurationRepository implements Repository
{
    public function find(string $code): Configuration
    {
        return Configuration::fromArray(
            'enabledConfiguration',
            [
                'isEnabled' => true,
                'identityProvider' => [
                    'entityId' => 'http://www.example.com/',
                    'signOnUrl' => 'http://www.example.com/signon',
                    'logoutUrl' => 'http://www.example.com/logout',
                    'certificate' => 'my mock certificate'
                ],
                'serviceProvider' => [
                    'entityId' => 'http://www.example.com/',
                    'certificate' => 'my mock certificate',
                    'privateKey' => 'my mock private key'
                ]
            ]
        );
    }

    public function save(Configuration $configurationRoot): void
    {
        throw new \LogicException("Mock configuration repository will not save configuration.");
    }
}
