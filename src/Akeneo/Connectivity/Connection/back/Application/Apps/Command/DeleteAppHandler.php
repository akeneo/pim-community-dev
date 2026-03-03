<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserGroupInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserRoleInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppDeletionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveRevokedAccessTokensOfDisconnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAppHandler
{
    public function __construct(
        private GetAppDeletionQueryInterface $getAppDeletionQuery,
        private DeleteConnectedAppQueryInterface $deleteConnectedAppQuery,
        private ConnectionRepositoryInterface $connectionRepository,
        private DeleteUserInterface $deleteUser,
        private DeleteClientInterface $deleteClient,
        private DeleteUserGroupInterface $deleteUserGroup,
        private DeleteUserRoleInterface $deleteUserRole,
        private SaveRevokedAccessTokensOfDisconnectedAppQueryInterface $saveRevokedAccessTokensOfDisconnectedAppQuery
    ) {
    }

    public function handle(DeleteAppCommand $command): void
    {
        $appDeletion = $this->getAppDeletionQuery->execute($command->getAppId());

        $this->saveRevokedAccessTokensOfDisconnectedAppQuery->execute($appDeletion->getAppId());

        $this->deleteConnectedAppQuery->execute($appDeletion->getAppId());

        $connection = $this->connectionRepository->findOneByCode($appDeletion->getConnectionCode());
        if (null === $connection) {
            throw new \InvalidArgumentException(
                \sprintf('Connection with code "%s" does not exist', $appDeletion->getConnectionCode())
            );
        }

        $this->connectionRepository->delete($connection);

        $this->deleteClient->execute($connection->clientId());
        $this->deleteUser->execute($connection->userId());
        $this->deleteUserGroup->execute($appDeletion->getUserGroupName());
        $this->deleteUserRole->execute($appDeletion->getUserRole());
    }
}
