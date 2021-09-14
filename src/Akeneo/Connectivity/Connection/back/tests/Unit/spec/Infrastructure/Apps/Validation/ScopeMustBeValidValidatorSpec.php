<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ScopeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ScopeMustBeValidValidator;
use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapperInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ScopeMustBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ScopeMapperInterface $scopeMapper,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($scopeMapper);
        $this->initialize($context);
    }

    public function it_is_an_app_authorization_session(): void
    {
        $this->shouldHaveType(ScopeMustBeValidValidator::class);
    }

    public function it_throw_if_not_the_excepted_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_validate_that_the_scope_contains_valid_values(
        ScopeMustBeValid $constraint,
        ScopeMapperInterface $scopeMapper,
        ExecutionContextInterface $context
    ): void {
        $scopeMapper->getAllScopes()->willReturn([
            'foo',
            'bar',
        ]);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('foo bar', $constraint);
    }

    public function it_adds_a_violation_when_at_least_one_scope_is_unknown(
        ScopeMustBeValid $constraint,
        ScopeMapperInterface $scopeMapper,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $scopeMapper->getAllScopes()->willReturn([
            'foo',
            'bar',
        ]);
        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('foo bar invalid', $constraint);
    }
}
