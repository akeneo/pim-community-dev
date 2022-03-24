<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserGroupInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\DeleteUserRoleInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppDeletion;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppDeletionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveRevokedAccessTokensOfDisconnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAppHandlerSpec extends ObjectBehavior
{
    public function let(
        GetAppDeletionQueryInterface $getAppDeletionQuery,
        DeleteConnectedAppQueryInterface $deleteConnectedAppQuery,
        ConnectionRepositoryInterface $connectionRepository,
        DeleteUserInterface $deleteUser,
        DeleteClientInterface $deleteClient,
        DeleteUserGroupInterface $deleteUserGroup,
        DeleteUserRoleInterface $deleteUserRole,
        SaveRevokedAccessTokensOfDisconnectedAppQueryInterface $saveRevokedAccessTokensOfDisconnectedAppQuery
    ): void {
        $this->beConstructedWith(
            $getAppDeletionQuery,
            $deleteConnectedAppQuery,
            $connectionRepository,
            $deleteUser,
            $deleteClient,
            $deleteUserGroup,
            $deleteUserRole,
            $saveRevokedAccessTokensOfDisconnectedAppQuery
        );
    }

    public function it_is_a_delete_app_handler(): void
    {
        $this->shouldHaveType(DeleteAppHandler::class);
    }

    public function it_deletes_an_app(
        GetAppDeletionQueryInterface $getAppDeletionQuery,
        ConnectionRepositoryInterface $connectionRepository,
        DeleteUserInterface $deleteUser,
        DeleteClientInterface $deleteClient,
        DeleteUserGroupInterface $deleteUserGroup,
        DeleteUserRoleInterface $deleteUserRole,
        SaveRevokedAccessTokensOfDisconnectedAppQueryInterface $saveRevokedAccessTokensOfDisconnectedAppQuery,
        Connection $connection,
        ClientId $clientId,
        UserId $userId
    ): void {
        $command = new DeleteAppCommand('app_id');
        $appDeletion = new AppDeletion(
            'app_id',
            'connection_code',
            'app_user_group_name',
            'ROLE_APP'
        );

        $getAppDeletionQuery->execute('app_id')->willReturn($appDeletion);
        $connectionRepository->findOneByCode('connection_code')->willReturn($connection);
        $connection->clientId()->willReturn($clientId);
        $connection->userId()->willReturn($userId);

        $saveRevokedAccessTokensOfDisconnectedAppQuery->execute('app_id')->shouldBeCalled();
        $connectionRepository->delete($connection)->shouldBeCalled();
        $deleteClient->execute($clientId)->shouldBeCalled();
        $deleteUser->execute($userId)->shouldBeCalled();
        $deleteUserGroup->execute('app_user_group_name')->shouldBeCalled();
        $deleteUserRole->execute('ROLE_APP')->shouldBeCalled();

        $this->handle($command);
    }
}
