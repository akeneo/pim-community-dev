<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Component\Catalog\Validator\Constraints\MetadataLength;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadata;
use Symfony\Component\Validator\MetadataInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class MetadataLengthValidatorSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $em, ExecutionContext $context)
    {
        $this->beConstructedWith($em);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\MetadataLengthValidator');
    }

    function it_is_a_validator()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_is_only_applied_on_non_empty_fields($context, Constraint $constraint)
    {
        $context->getObject()->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('', $constraint);
        $this->validate(null, $constraint);
    }

    function it_is_applied_only_on_objects($context, Constraint $constraint)
    {
        $context->getObject()->willReturn('a_string');
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('my_translation', $constraint);
    }

    function it_validates_the_value_is_not_to_long(
        $context,
        $em,
        MetadataLength $constraint,
        AttributeOptionValue $optionValue,
        PropertyMetadata $propertyMetadata,
        ClassMetadata $classMetadata
    ) {
        $context->getObject()->willReturn($optionValue);
        $context->getMetadata()->willReturn($propertyMetadata);
        $propertyMetadata->getPropertyName()->willReturn('value');

        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->getFieldMapping('value')->willReturn(['length' => 20]);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('my_translation', $constraint);
    }

    function it_adds_a_violation_when_the_value_is_too_long(
        $context,
        $em,
        MetadataLength $constraint,
        AttributeOptionValue $optionValue,
        PropertyMetadata $propertyMetadata,
        ClassMetadata $classMetadata,
        ConstraintViolationBuilder $constraintViolationBuilder
    ) {
        $context->getObject()->willReturn($optionValue);
        $context->getMetadata()->willReturn($propertyMetadata);
        $propertyMetadata->getPropertyName()->willReturn('value');

        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->getFieldMapping('value')->willReturn(['length' => 10]);

        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('{{ limit }}', 10)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setInvalidValue('my_translation')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate('my_translation', $constraint);
    }
}
