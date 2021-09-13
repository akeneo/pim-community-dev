<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration\Application;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;

/**
 * Persists data contained in a CreateOrUpdateConfiguration command.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class CreateOrUpdateConfigurationHandler
{
    /** @var Repository */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(CreateOrUpdateConfiguration $command): void
    {
        $config = new Configuration(
            new Code($command->code),
            new IsEnabled($command->isEnabled),
            new IdentityProvider(
                new EntityId($command->identityProviderEntityId),
                new Url($command->identityProviderSignOnUrl),
                new Url($command->identityProviderLogoutUrl),
                new Certificate($command->identityProviderCertificate)
            ),
            new ServiceProvider(
                new EntityId($command->serviceProviderEntityId),
                new Certificate($command->serviceProviderCertificate),
                new Certificate($command->serviceProviderPrivateKey)
            )
        );

        $this->repository->save($config);
    }
}
