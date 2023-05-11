<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationHandlerSpec extends ObjectBehavior
{
    public function let(
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        ClockInterface $clock,
        ValidatorInterface $validator
    ): void {
        $clock->now()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2021-02-03T00:00:00Z')
        );

        $this->beConstructedWith(
            $getAppConfirmationQuery,
            $appAuthorizationSession,
            $createUserConsentQuery,
            $clock,
            $validator
        );
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(ConsentAppAuthenticationHandler::class);
    }

    public function it_creates_the_user_consent(
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        ClockInterface $clock
    ): void {
        $userGroup = 'a_user_group';
        $clientId = 'a_client_id';
        $pimUserId = 1;
        $fosClientId = 2;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $consentAppAuthenticationCommand->getClientId(),
            'authorization_scope' => ScopeList::fromScopes([])->toScopeString(),
            'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a_state',
        ]);
        $appConfirmation = AppConfirmation::create(
            $consentAppAuthenticationCommand->getClientId(),
            $pimUserId,
            $userGroup,
            $fosClientId
        );

        $constraintViolationList->count()->willReturn(0);
        $appAuthorizationSession->getAppAuthorization($consentAppAuthenticationCommand->getClientId())->willReturn(
            $appAuthorization
        );
        $getAppConfirmationQuery->execute($consentAppAuthenticationCommand->getClientId())->willReturn(
            $appConfirmation
        );
        $validator->validate($consentAppAuthenticationCommand)->willReturn($constraintViolationList);

        $createUserConsentQuery->execute(
            $consentAppAuthenticationCommand->getPimUserId(),
            $appConfirmation->getAppId(),
            $appAuthorization->getAuthenticationScopes()->getScopes(),
            Argument::any()
        )->shouldBeCalledOnce();

        $this->handle($consentAppAuthenticationCommand);
    }

    public function it_throws_when_the_command_is_not_valid(
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $clientId = 'a_client_id';
        $pimUserId = 1;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $constraintViolation = new ConstraintViolation('a_violated_constraint', '', [], '', '', '');

        $constraintViolationList->count()->willReturn(1);
        $constraintViolationList->get(0)->willReturn($constraintViolation);

        $validator->validate($consentAppAuthenticationCommand)->willReturn($constraintViolationList);

        $this->shouldThrow(new InvalidAppAuthenticationException($constraintViolationList->getWrappedObject()))->during(
            'handle',
            [$consentAppAuthenticationCommand]
        );
    }

    public function it_throws_when_the_app_authorization_is_not_found(
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $clientId = 'a_client_id';
        $pimUserId = 1;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $constraintViolationList->count()->willReturn(0);
        $appAuthorizationSession->getAppAuthorization($consentAppAuthenticationCommand->getClientId())->willReturn(
            null
        );

        $validator->validate($consentAppAuthenticationCommand)->willReturn($constraintViolationList);

        $this->shouldThrow(new \LogicException('There is no active app authorization in session'))->during(
            'handle',
            [$consentAppAuthenticationCommand]
        );
    }

    public function it_throws_when_the_app_confirmation_is_not_found(
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $clientId = 'a_client_id';
        $pimUserId = 1;
        $consentAppAuthenticationCommand = new ConsentAppAuthenticationCommand($clientId, $pimUserId);
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $consentAppAuthenticationCommand->getClientId(),
            'authorization_scope' => ScopeList::fromScopes([])->toScopeString(),
            'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a_state',
        ]);

        $constraintViolationList->count()->willReturn(0);
        $appAuthorizationSession->getAppAuthorization($consentAppAuthenticationCommand->getClientId())->willReturn(
            $appAuthorization
        );
        $getAppConfirmationQuery->execute($consentAppAuthenticationCommand->getClientId())->willReturn(
            null
        );
        $validator->validate($consentAppAuthenticationCommand)->willReturn($constraintViolationList);

        $this->shouldThrow(new \LogicException('The connected app should have been created'))->during(
            'handle',
            [$consentAppAuthenticationCommand]
        );
    }
}
