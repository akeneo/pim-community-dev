<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustHaveOngoingAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustHaveOngoingAuthorizationValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ClientIdMustHaveOngoingAuthorizationValidatorSpec extends ObjectBehavior
{
    public function let(
        AppAuthorizationSessionInterface $session,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($session);
        $this->initialize($context);
    }

    public function it_is_an_app_authorization_session(): void
    {
        $this->shouldHaveType(ClientIdMustHaveOngoingAuthorizationValidator::class);
    }

    public function it_throw_if_not_the_excepted_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_validate_that_the_client_id_has_an_authorization_session(
        ClientIdMustHaveOngoingAuthorization $constraint,
        AppAuthorizationSessionInterface $session,
        AppAuthorization $appAuthorization,
        ExecutionContextInterface $context
    ): void {
        $session->getAppAuthorization('app_id')->willReturn($appAuthorization);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('app_id', $constraint);
    }

    public function it_adds_a_violation_when_client_id_has_not_an_authorization_session(
        ClientIdMustHaveOngoingAuthorization $constraint,
        AppAuthorizationSessionInterface $session,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $session->getAppAuthorization('app_id')->willReturn(null);
        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('app_id', $constraint);
    }
}
