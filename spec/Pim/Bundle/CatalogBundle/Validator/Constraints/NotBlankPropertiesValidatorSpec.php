<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Validator\Constraints\NotBlankProperties;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotBlankPropertiesValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\NotBlankPropertiesValidator');
    }

    function it_validates_not_blank_property_value(
        $context,
        NotBlankProperties $constraint,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => 'not_blank_value']);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_validate_blank_property_value(
        $context,
        NotBlankProperties $constraint,
        ConstraintViolationBuilderInterface $violation,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => null]);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }
}
