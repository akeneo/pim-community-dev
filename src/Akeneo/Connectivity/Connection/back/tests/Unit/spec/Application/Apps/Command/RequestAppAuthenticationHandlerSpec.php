<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationHandlerSpec extends ObjectBehavior
{
    public function let(
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        ClockInterface $clock,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(
            $getUserConsentedAuthenticationScopesQuery,
            $createUserConsentQuery,
            $clock,
            $validator
        );
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(RequestAppAuthenticationHandler::class);
    }

    public function it_throws_when_the_command_is_invalid(
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList,
        ConstraintViolationInterface $constraintViolation
    ): void {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString(''));

        $validator->validate($command)
            ->willReturn($constraintViolationList);
        $constraintViolationList->count()
            ->willReturn(1);
        $constraintViolationList->get(0)
            ->willReturn($constraintViolation);
        $constraintViolation->getMessage()
            ->willReturn('a_validation_error');

        $this->shouldThrow(new \InvalidArgumentException('a_validation_error'))->during('handle', [$command]);
    }

    public function it_clears_consented_scopes_when_openid_is_not_requested(
        ValidatorInterface $validator,
        ClockInterface $clock,
        CreateUserConsentQueryInterface $createUserConsentQuery
    ): void {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('a_scope'));

        $validator->validate($command)
            ->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $clock->now()
            ->willReturn($dateTime);

        $createUserConsentQuery->execute(1, 'a_app_id', [], $dateTime)
            ->shouldBeCalledOnce();

        $this->handle($command);
    }

    public function it_removes_previously_consented_scopes_that_are_not_requested_anymore(
        ValidatorInterface $validator,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        ClockInterface $clock,
        CreateUserConsentQueryInterface $createUserConsentQuery
    ): void {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('openid a_scope'));

        $validator->validate($command)
            ->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $clock->now()
            ->willReturn($dateTime);

        $getUserConsentedAuthenticationScopesQuery->execute(1, 'a_app_id')
            ->willReturn(['openid', 'a_scope', 'a_scope_not_requested']);

        $createUserConsentQuery->execute(1, 'a_app_id', ['openid', 'a_scope'], $dateTime)
            ->shouldBeCalledOnce();

        $this->handle($command);
    }

    public function it_consents_automatically_when_openid_is_the_only_scope_requested(
        ValidatorInterface $validator,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        ClockInterface $clock,
        CreateUserConsentQueryInterface $createUserConsentQuery
    ): void {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('openid'));

        $validator->validate($command)
            ->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $clock->now()
            ->willReturn($dateTime);

        $getUserConsentedAuthenticationScopesQuery->execute(1, 'a_app_id')
            ->willReturn([]);

        $createUserConsentQuery->execute(1, 'a_app_id', ['openid'], $dateTime)
            ->shouldBeCalledOnce();

        $this->handle($command);
    }

    public function it_throws_when_new_scopes_are_requiring_consent(
        ValidatorInterface $validator,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        ClockInterface $clock
    ): void {
        $command = new RequestAppAuthenticationCommand('a_app_id', 1, ScopeList::fromScopeString('openid a_new_scope'));

        $validator->validate($command)
            ->willReturn(new ConstraintViolationList());
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-01-01T00:00:00Z');
        $clock->now()
            ->willReturn($dateTime);

        $getUserConsentedAuthenticationScopesQuery->execute(1, 'a_app_id')
            ->willReturn(['openid']);

        $exception = new UserConsentRequiredException('a_app_id', 1);
        $this->shouldThrow($exception)->during('handle', [$command]);
    }
}
