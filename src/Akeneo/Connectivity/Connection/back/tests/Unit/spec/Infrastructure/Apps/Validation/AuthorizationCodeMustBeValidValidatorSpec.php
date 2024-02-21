<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustBeValidValidator;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AuthorizationCodeMustBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        IOAuth2GrantCode $storage,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($storage);
        $this->initialize($context);
    }

    public function it_is_an_authorization_code_validator(): void
    {
        $this->shouldHaveType(AuthorizationCodeMustBeValidValidator::class);
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    public function it_validates_the_authorization_code(
        IOAuth2GrantCode $storage,
        ExecutionContextInterface $context,
        IOAuth2AuthCode $authCode
    ): void {
        $constraint = new AuthorizationCodeMustBeValid();
        $authCode->getClientId()->willReturn('client_id');
        $authCode->hasExpired()->willReturn(false);

        $storage->getAuthCode('auth_code_1234')->willReturn($authCode);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('auth_code_1234', $constraint);
    }

    public function it_builds_a_violation_if_the_authorization_code_is_invalid(
        IOAuth2GrantCode $storage,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $constraint = new AuthorizationCodeMustBeValid();

        $storage->getAuthCode('auth_code_1234')->willReturn(null);
        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setCause($constraint->cause)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('auth_code_1234', $constraint);
    }

    public function it_processes_only_string(): void
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException('The value to validate must be a string')
            )
            ->during('validate', [12345, new AuthorizationCodeMustBeValid()]);
    }

    public function it_validates_the_value_only_if_the_provided_constraint_is_matching_the_validator(): void
    {
        $this
            ->shouldThrow(
                UnexpectedTypeException::class
            )
            ->during('validate', ['auth_code_1234', new class() extends Constraint {
            }]);
    }
}
