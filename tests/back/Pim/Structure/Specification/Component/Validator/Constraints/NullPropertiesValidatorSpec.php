<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NullPropertiesValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NullProperties;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NullPropertiesValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NullPropertiesValidator::class);
    }

    function it_validates_null_property_value(
        $context,
        NullProperties $constraint,
        Attribute $attribute
    ) {
        $constraint->properties = ['my_property'];

        $attribute
            ->getProperties()
            ->willReturn(['my_property' => null]);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_validate_not_null_property_value(
        $context,
        NullProperties $constraint,
        ConstraintViolationBuilderInterface $violationBuilder,
        Attribute $attribute
    ) {
        $constraint->properties = ['my_property'];

        $attribute
            ->getProperties()
            ->willReturn(['my_property' => 'not null value']);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder
            ->atPath('my_property')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
