<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustNotBeExpired;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\AuthorizationCodeMustNotBeExpiredValidator;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeMustNotBeExpiredValidatorSpec extends ObjectBehavior
{
    public function let(
        IOAuth2GrantCode $storage,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($storage);
        $this->initialize($context);
    }

    public function it_is_an_expired_authorization_code_validator(): void
    {
        $this->shouldHaveType(AuthorizationCodeMustNotBeExpiredValidator::class);
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
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

    public function it_validates_only_string_value(): void
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException('The value to validate must be a string')
            )
            ->during('validate', [12345, new AuthorizationCodeMustNotBeExpired()]);
    }

    public function it_validates_the_authorization_code(
        IOAuth2GrantCode $storage,
        ExecutionContextInterface $context,
        IOAuth2AuthCode $authCode
    ): void {
        $authCode->getClientId()->willReturn('client_id');
        $authCode->hasExpired()->willReturn(false);

        $storage->getAuthCode('auth_code_1234')->willReturn($authCode);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('auth_code_1234', new AuthorizationCodeMustNotBeExpired());
    }

    public function it_builds_a_violation_if_the_authorization_code_is_expired(
        IOAuth2GrantCode $storage,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        IOAuth2AuthCode $authCode
    ): void {
        $constraint = new AuthorizationCodeMustNotBeExpired();
        $authCode->getClientId()->willReturn('client_id');
        $authCode->hasExpired()->willReturn(true);

        $storage->getAuthCode('auth_code_1234')->willReturn($authCode);
        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setCause($constraint->cause)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('auth_code_1234', $constraint);
    }
}
