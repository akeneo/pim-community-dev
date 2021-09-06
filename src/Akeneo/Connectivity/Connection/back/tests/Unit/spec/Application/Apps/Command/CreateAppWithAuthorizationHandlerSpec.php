<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use PhpSpec\ObjectBehavior;
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
        AppAuthorizationSessionInterface $session,
        GetAppQueryInterface $getAppQuery,
        CreateUserInterface $createUser,
        CreateUserGroupInterface $createUserGroup,
        CreateConnectionInterface $createConnection,
        AppRoleWithScopesFactoryInterface $roleFactory,
        ClientProviderInterface $clientProvider,
        CreateAppInterface $createApp
    ): void
    {
        $this->beConstructedWith(
            $validator,
            $session,
            $getAppQuery,
            $createUser,
            $createUserGroup,
            $createConnection,
            $roleFactory,
            $clientProvider,
            $createApp
        );
    }

    public function it_is_instantiable()
    {
        $this->shouldHaveType(CreateAppWithAuthorizationHandler::class);
    }

    public function it_throws_exception_when_handling_invalid_command(ValidatorInterface $validator): void
    {
        $invalidCommand = new CreateAppWithAuthorizationCommand('');

        $validator
            ->validate($invalidCommand)
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('Not Blank', '', [], '', 'clientId', ''),
            ]));

        $this
            ->shouldThrow(InvalidAppAuthorizationRequest::class)
            ->during('handle', [$invalidCommand]);
    }

    public function it_throws_exception_when_app_is_not_found(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery
    ): void {
        $appId = 'test_app_id';
        $invalidCommand = new CreateAppWithAuthorizationCommand($appId);

        $validator
            ->validate($invalidCommand)
            ->willReturn(new ConstraintViolationList([]));

        $getAppQuery->execute($appId)->willReturn(null);

        $this
            ->shouldThrow(\RuntimeException::class)
            ->during('handle', [$invalidCommand]);
    }
}
