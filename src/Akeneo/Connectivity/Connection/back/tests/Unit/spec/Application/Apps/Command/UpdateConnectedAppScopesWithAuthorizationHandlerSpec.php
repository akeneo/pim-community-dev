<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\UpdateConnectedAppRoleWithScopesInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesWithAuthorizationHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        UpdateConnectedAppScopesQueryInterface $updateConnectedAppScopesQuery,
        UpdateConnectedAppRoleWithScopesInterface $updateConnectedAppRoleWithScopes,
    ): void {
        $this->beConstructedWith(
            $validator,
            $appAuthorizationSession,
            $updateConnectedAppScopesQuery,
            $updateConnectedAppRoleWithScopes,
        );
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(UpdateConnectedAppScopesWithAuthorizationHandler::class);
    }

    public function it_throws_when_the_command_is_not_valid(ValidatorInterface $validator): void
    {
        $command = new UpdateConnectedAppScopesWithAuthorizationCommand('');

        $validator
            ->validate($command)
            ->willReturn(
                new ConstraintViolationList([
                    new ConstraintViolation('Not Blank', '', [], '', 'clientId', ''),
                ])
            );

        $this
            ->shouldThrow(InvalidAppAuthorizationRequestException::class)
            ->during('handle', [$command]);
    }

    public function it_throws_when_the_app_authorization_was_not_found_despite_validation(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
    ): void {
        $command = new UpdateConnectedAppScopesWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('handle', [$command]);
    }

    public function it_updates_a_connected_app_when_everything_is_valid(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        UpdateConnectedAppScopesQueryInterface $updateConnectedAppScopesQuery,
        UpdateConnectedAppRoleWithScopesInterface $updateConnectedAppRoleWithScopes,
        AppAuthorization $appAuthorization,
    ): void {
        $command = new UpdateConnectedAppScopesWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn($appAuthorization);

        $appAuthorization->getAuthorizationScopes()->willReturn(ScopeList::fromScopes(['a_scope']));

        $updateConnectedAppScopesQuery
            ->execute(['a_scope'], 'an_app_id')
            ->shouldBeCalled();

        $updateConnectedAppRoleWithScopes
            ->execute('an_app_id', ['a_scope'])
            ->shouldBeCalled();

        $this->handle($command);
    }
}
