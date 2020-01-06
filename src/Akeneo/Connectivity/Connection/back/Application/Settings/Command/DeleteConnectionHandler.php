<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionHandler
{
    /** @var ConnectionRepository */
    private $repository;

    /** @var DeleteClientInterface */
    private $deleteClient;

    /** @var DeleteUserInterface */
    private $deleteUser;

    public function __construct(
        ConnectionRepository $repository,
        DeleteClientInterface $deleteClient,
        DeleteUserInterface $deleteUser
    ) {
        $this->repository = $repository;
        $this->deleteClient = $deleteClient;
        $this->deleteUser = $deleteUser;
    }

    public function handle(DeleteConnectionCommand $command): void
    {
        $connection = $this->repository->findOneByCode($command->code());
        if (null === $connection) {
            throw new \InvalidArgumentException(
                sprintf('Connection with code "%s" does not exist', $command->code())
            );
        }

        $this->repository->delete($connection);

        $this->deleteUser->execute($connection->userId());
        $this->deleteClient->execute($connection->clientId());
    }
}
