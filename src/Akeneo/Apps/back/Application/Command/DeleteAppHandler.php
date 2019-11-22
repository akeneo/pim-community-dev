<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Service\DeleteClientInterface;
use Akeneo\Apps\Application\Service\DeleteUserInterface;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteAppHandler
{
    /** @var AppRepository */
    private $repository;

    /** @var DeleteClientInterface */
    private $deleteClient;

    /** @var DeleteUserInterface */
    private $deleteUser;

    public function __construct(
        AppRepository $repository,
        DeleteClientInterface $deleteClient,
        DeleteUserInterface $deleteUser
    ) {
        $this->repository = $repository;
        $this->deleteClient = $deleteClient;
        $this->deleteUser = $deleteUser;
    }

    public function handle(DeleteAppCommand $command): void
    {
        $app = $this->repository->findOneByCode($command->code());
        if (null === $app) {
            throw new \InvalidArgumentException(
                sprintf('App with code "%s" does not exist', $command->code())
            );
        }

        $this->repository->delete($app);

        $this->deleteUser->execute($app->userId());
        $this->deleteClient->execute($app->clientId());
    }
}
