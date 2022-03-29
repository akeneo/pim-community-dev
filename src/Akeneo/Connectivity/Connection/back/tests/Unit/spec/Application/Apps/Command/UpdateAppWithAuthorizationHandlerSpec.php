<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAppWithAuthorizationHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        UpdateConnectedAppQueryInterface $updateConnectedAppQuery,
    ): void {
        $this->beConstructedWith(
            $validator,
            $appAuthorizationSession,
            $updateConnectedAppQuery,
        );
    }

    public function it_is_instantiable()
    {
        $this->shouldHaveType(UpdateAppWithAuthorizationHandler::class);
    }

    public function it_throws_when_the_command_is_not_valid(ValidatorInterface $validator): void
    {
        $command = new UpdateAppWithAuthorizationCommand('');

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
        $command = new UpdateAppWithAuthorizationCommand('an_app_id');

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
        UpdateConnectedAppQueryInterface $updateConnectedAppQuery,
        AppAuthorization $appAuthorization,
    ): void {
        $command = new UpdateAppWithAuthorizationCommand('an_app_id');

        $validator
            ->validate($command)
            ->willReturn(new ConstraintViolationList([]));

        $appAuthorizationSession->getAppAuthorization('an_app_id')->willReturn($appAuthorization);

        $appAuthorization->getAuthorizationScopes()->willReturn(ScopeList::fromScopes(['a_scope']));

        $updateConnectedAppQuery
            ->execute(['a_scope'], 'an_app_id')
            ->shouldBeCalled();

        $this->handle($command);
    }
}
