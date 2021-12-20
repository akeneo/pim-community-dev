<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAppWithAuthorizationHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        GetAppQueryInterface $getAppQuery,
        CreateUserInterface $createUser,
        CreateUserGroupInterface $createUserGroup,
        CreateConnectionInterface $createConnection,
        AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory,
        ClientProviderInterface $clientProvider,
        CreateConnectedAppInterface $createApp
    ): void {
        $this->beConstructedWith(
            $validator,
            $appAuthorizationSession,
            $getAppQuery,
            $createUser,
            $createUserGroup,
            $createConnection,
            $appRoleWithScopesFactory,
            $clientProvider,
            $createApp
        );
    }

    public function it_is_instantiable()
    {
        $this->shouldHaveType(CreateAppWithAuthorizationHandler::class);
    }

    public function it_throws_when_the_command_is_not_valid(ValidatorInterface $validator): void
    {
        $command = new CreateAppWithAuthorizationCommand('');

        $validator
            ->validate($command)
            ->willReturn(
                new ConstraintViolationList([
                    new ConstraintViolation('Not Blank', '', [], '', 'clientId', ''),
                ])
            );

        $this
            ->shouldThrow(InvalidAppAuthorizationRequest::class)
            ->during('handle', [$command]);
    }

    public function it_throws_when_the_app_was_not_found_despite_validation(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery
    ): void {
        $command = new CreateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute('an_app_id')->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_when_the_app_authorization_was_not_found_despite_validation(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        App $app
    ): void {
        $command = new CreateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute('an_app_id')->willReturn($app);
        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_when_the_client_was_not_found_despite_validation(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ClientProviderInterface $clientProvider,
        App $app,
        AppAuthorization $appAuthorization
    ): void {
        $command = new CreateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute('an_app_id')->willReturn($app);
        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn($appAuthorization);
        $clientProvider->findClientByAppId('an_app_id')->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_when_the_created_group_is_invalid(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ClientProviderInterface $clientProvider,
        CreateUserGroupInterface $createUserGroup,
        App $app,
        AppAuthorization $appAuthorization,
        Client $client,
        GroupInterface $userGroup
    ): void {
        $command = new CreateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute('an_app_id')->willReturn($app);
        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn($appAuthorization);
        $clientProvider->findClientByAppId('an_app_id')->willReturn($client);
        $createUserGroup->execute(Argument::any())->willReturn($userGroup);
        $userGroup->getName()->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_when_the_created_role_is_invalid(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ClientProviderInterface $clientProvider,
        CreateUserGroupInterface $createUserGroup,
        AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory,
        App $app,
        AppAuthorization $appAuthorization,
        Client $client,
        GroupInterface $userGroup,
        RoleInterface $role
    ): void {
        $command = new CreateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute('an_app_id')->willReturn($app);
        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn($appAuthorization);
        $appAuthorization->getAuthorizationScopes()->willReturn(ScopeList::fromScopes([]));
        $clientProvider->findClientByAppId('an_app_id')->willReturn($client);
        $createUserGroup->execute(Argument::any())->willReturn($userGroup);
        $userGroup->getName()->willReturn('foo');
        $appRoleWithScopesFactory->createRole('an_app_id', [])->willReturn($role);
        $role->getRole()->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('handle', [$command]);
    }

    public function it_create_a_connection_when_everything_is_valid(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ClientProviderInterface $clientProvider,
        CreateUserGroupInterface $createUserGroup,
        AppRoleWithScopesFactoryInterface $appRoleWithScopesFactory,
        CreateUserInterface $createUser,
        CreateConnectedAppInterface $createApp,
        CreateConnectionInterface $createConnection,
        App $app,
        AppAuthorization $appAuthorization,
        Client $client,
        GroupInterface $userGroup,
        RoleInterface $role,
        User $user,
        ConnectionWithCredentials $connection
    ): void {
        $command = new CreateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute('an_app_id')->willReturn($app);
        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn($appAuthorization);
        $appAuthorization->getAuthorizationScopes()->willReturn(ScopeList::fromScopes(['a_scope']));
        $clientProvider->findClientByAppId('an_app_id')->willReturn($client);
        $createUserGroup->execute(Argument::any())->willReturn($userGroup);
        $userGroup->getName()->willReturn('a_group');
        $appRoleWithScopesFactory->createRole('an_app_id', ['a_scope'])->willReturn($role);
        $role->getRole()->willReturn('ROLE_APP');
        $createUser->execute(Argument::any(), Argument::any(), Argument::any(), ['a_group'], ['ROLE_APP'])->willReturn($user);

        $client->getId()->willReturn(42);
        $app->getName()->willReturn('My App');
        $user->id()->willReturn(43);
        $createConnection->execute(Argument::any(), 'My App', 'other', 42, 43)->willReturn($connection);
        $connection->code()->willReturn('random_connection_code');

        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group'
        );
        $createApp
            ->execute($app, ['a_scope'], 'random_connection_code', 'a_group')
            ->willReturn($connectedApp)
            ->shouldBeCalled();

        $this->handle($command);
    }
}
