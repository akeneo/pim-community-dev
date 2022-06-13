<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserShouldExist;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserShouldExistValidator;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserShouldExistValidatorSpec extends ObjectBehavior
{
    function let(UserRepositoryInterface $userRepository, ExecutionContext $context)
    {
        $userRepository->findOneBy(['id' => 1])->willReturn(new User());
        $userRepository->findOneBy(['id' => 2])->willReturn(null);

        $this->beConstructedWith($userRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserShouldExistValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [1, new Type([])]);
    }

    function it_does_nothing_when_the_value_is_not_an_integer(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('1', new UserShouldExist());
        $this->validate(null, new UserShouldExist());
    }

    function it_adds_a_violation_when_user_is_unknown(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new UserShouldExist();
        $context->buildViolation($constraint->message, ['{{ user_id }}' => 2])->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->setCode((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(2, $constraint);
    }

    function it_validates_when_user_exists(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate(1, new UserShouldExist());
    }
}
