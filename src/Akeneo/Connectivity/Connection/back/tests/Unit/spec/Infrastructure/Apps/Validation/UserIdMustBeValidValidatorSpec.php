<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\UserIdMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\UserIdMustBeValidValidator;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserIdMustBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        UserRepositoryInterface $userRepository,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($userRepository);
        $this->initialize($context);
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(UserIdMustBeValidValidator::class);
    }

    public function it_throw_if_not_the_excepted_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_validates_that_the_user_exists(
        UserIdMustBeValid $constraint,
        UserRepositoryInterface $userRepository,
        UserInterface $user,
        ExecutionContextInterface $context
    ): void {
        $userRepository->find(1)->willReturn($user);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(1, $constraint);
    }

    public function it_adds_a_violation_when_the_user_doesnt_exist(
        UserIdMustBeValid $constraint,
        UserRepositoryInterface $userRepository,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $userRepository->find(1)->willReturn(null);
        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate(1, $constraint);
    }
}
