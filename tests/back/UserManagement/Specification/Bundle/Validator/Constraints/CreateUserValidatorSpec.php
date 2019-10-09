<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Bundle\Validator\Constraints\CreateUser;
use Akeneo\UserManagement\Bundle\Validator\Constraints\CreateUserValidator;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CreateUserValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateUserValidator::class);
    }

    function it_is_a_constraints_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_does_not_add_violation_if_created_user_is_valid(CreateUser $constraint, UserInterface $user)
    {
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn('foobar');
        $this->validate($user, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_if_created_user_has_space_in_username(
        UserInterface $user,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        CreateUser $constraint
    ) {
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn('foo bar');

        $context->buildViolation('The username should not contain space character.')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[username]')
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($user, $constraint);
    }

    function it_does_not_add_violation_if_it_is_not_a_created_user(CreateUser $constraint, UserInterface $user)
    {
        $user->getId()->willReturn(666);
        $user->getUsername()->willReturn('foo bar');
        $this->validate($user, $constraint)->shouldReturn(null);
    }
}
