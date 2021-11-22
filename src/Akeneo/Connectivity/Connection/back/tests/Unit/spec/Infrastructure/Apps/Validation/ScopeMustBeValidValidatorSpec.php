<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ScopeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ScopeMustBeValidValidator;
use Akeneo\Tool\Component\Api\Security\ScopeMapperInterface;
use Akeneo\Tool\Component\Api\Security\ScopeMapperRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ScopeMustBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ScopeMapperInterface $productScopeMapper,
        ExecutionContextInterface $context
    ): void {
        $productScopeMapper->getAllScopes()->willReturn([
            'read_products',
            'write_products',
        ]);
        $registry = new ScopeMapperRegistry([
            'product' => $productScopeMapper->getWrappedObject(),
        ]);
        $this->beConstructedWith($registry);
        $this->initialize($context);
    }

    public function it_is_an_app_authorization_session(): void
    {
        $this->shouldHaveType(ScopeMustBeValidValidator::class);
    }

    public function it_throws_if_not_the_excepted_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_validates_that_the_scope_contains_valid_values(
        ScopeMustBeValid $constraint,
        ExecutionContextInterface $context
    ): void {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('read_products write_products', $constraint);
    }

    public function it_adds_a_violation_when_at_least_one_scope_is_unknown(
        ScopeMustBeValid $constraint,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('read_products write_products invalid', $constraint);
    }
}
