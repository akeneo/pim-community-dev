<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CustomAction as CustomActionDTO;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CustomAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\CustomActionValidator;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CustomActionValidatorSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $chainedDenormalizer, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($chainedDenormalizer);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(CustomActionValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new CustomActionDTO([]), new IsNull()]
        );
    }

    function it_throws_an_exception_if_value_is_not_a_custom_action()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new \stdClass(), new CustomAction()]);
    }

    function it_does_not_validate_an_action_with_a_null_type(ExecutionContextInterface $context)
    {
        $customAction = new CustomActionDTO(['foo' => 'bar']);
        $constraint = new CustomAction();

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($customAction, $constraint);
    }

    function it_does_not_validate_an_action_with_a_non_string_type(ExecutionContextInterface $context)
    {
        $customAction = new CustomActionDTO(['foo' => 'bar', 'type' => ['bar' => 'baz']]);
        $constraint = new CustomAction();

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($customAction, $constraint);
    }

    function it_builds_a_violation_if_custom_action_cannot_be_denormalized(
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $customAction = new CustomActionDTO(['type' => 'custom', 'foo' => 'bar']);
        $constraint = new CustomAction();

        $chainedDenormalizer->supportsDenormalization(['type' => 'custom', 'foo' => 'bar'], 'custom')->shouldBeCalled()
                            ->willReturn(false);
        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('type')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('custom')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($customAction, $constraint);
    }

    function it_validates_the_denormalized_custom_action(
        DenormalizerInterface $chainedDenormalizer,
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ActionInterface $denormalizedAction
    ) {
        $customAction = new CustomActionDTO(['type' => 'custom', 'foo' => 'bar']);
        $constraint = new CustomAction();

        $chainedDenormalizer->supportsDenormalization(['type' => 'custom', 'foo' => 'bar'], 'custom')->shouldBeCalled()
                            ->willReturn(true);
        $chainedDenormalizer->denormalize($customAction->toArray(), 'custom')->shouldBeCalled()->willReturn($denormalizedAction);

        $context->getValidator()->shouldBeCalled()->willReturn($validator);
        $validator->inContext($context)->shouldBeCalled()->willReturn($validator);

        $validator->validate($denormalizedAction)->shouldBeCalled();

        $this->validate($customAction, $constraint);
    }
}
