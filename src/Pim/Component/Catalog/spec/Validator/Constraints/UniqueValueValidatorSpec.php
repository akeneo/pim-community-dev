<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueValue;
use Pim\Component\Catalog\Validator\UniqueValuesSet;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueValueValidatorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        UniqueValuesSet $uniqueValuesSet,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($productRepository, $uniqueValuesSet);
        $this->initialize($context);
    }

    function it_adds_violation_if_unique_value_exists_in_database(
        $uniqueValuesSet,
        $context,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        UniqueValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->isUnique()->willReturn(true);

        $productRepository->valueExists($productValue)->willReturn(true);

        $productValue->getProduct()->willReturn($product);
        $uniqueValuesSet->addValue($productValue)->willReturn(true);

        $productValue->getData()->willReturn('foo');
        $attribute->getCode()->willReturn('bar');

        $context->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->setParameter('%value%', 'foo')->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%attribute%', 'bar')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_adds_violation_if_same_unique_value_was_already_validated(
        $uniqueValuesSet,
        $context,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        UniqueValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->isUnique()->willReturn(true);

        $productRepository->valueExists($productValue)->willReturn(false);

        $productValue->getProduct()->willReturn($product);
        $uniqueValuesSet->addValue($productValue)->willReturn(false);

        $productValue->getData()->willReturn('foo');
        $attribute->getCode()->willReturn('bar');

        $context->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);
        $violation->setParameter('%value%', 'foo')->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%attribute%', 'bar')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_does_not_add_violation_for_unique_value(
        $uniqueValuesSet,
        $context,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        Constraint $constraint
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->isUnique()->willReturn(true);

        $productRepository->valueExists($productValue)->willReturn(false);

        $productValue->getProduct()->willReturn($product);
        $uniqueValuesSet->addValue($productValue)->willReturn(true);

        $productValue->getData()->shouldNotBeCalled();
        $attribute->getCode()->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }

    function it_does_not_add_violation_for_attribute_with_non_unique_value(
        $uniqueValuesSet,
        $context,
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ProductRepositoryInterface $productRepository,
        Constraint $constraint
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->isUnique()->willReturn(false);

        $productRepository->valueExists($productValue)->shouldNotBeCalled();
        $productValue->getProduct()->shouldNotBeCalled();
        $uniqueValuesSet->addValue($productValue)->shouldNotBeCalled();
        $productValue->getData()->shouldNotBeCalled();
        $attribute->getCode()->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($productValue, $constraint);
    }
}
