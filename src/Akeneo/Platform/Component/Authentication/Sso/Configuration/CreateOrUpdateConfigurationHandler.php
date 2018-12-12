<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;

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
            new IdentityProvider(
                new EntityId($command->identityProviderEntityId),
                new Url($command->identityProviderUrl),
                new Certificate($command->identityProviderPublicCertificate)
            ),
            new ServiceProvider(
                new EntityId($command->serviceProviderEntityId),
                new Certificate($command->serviceProviderPublicCertificate),
                new Certificate($command->serviceProviderPrivateCertificate)
            )
        );

        $this->repository->save($config);
    }
}
