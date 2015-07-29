<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue;
use Pim\Bundle\CatalogBundle\Validator\UniqueValuesSet;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueValueValidatorSpec extends ObjectBehavior
{
    const PROPERTY_PATH='children[values].children[unique_attribute].children[varchar].data';

    function let(
        ProductRepositoryInterface $productRepository,
        UniqueValuesSet $uniqueValuesSet,
        ExecutionContextInterface $context,
        Form $form,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $this->beConstructedWith($productRepository, $uniqueValuesSet);

        $product->getValue('unique_attribute')->willReturn($value);

        $form->getData()->willReturn($product);

        $context->getPropertyPath()->willReturn(self::PROPERTY_PATH);
        $context->getRoot()->willReturn($form);

        $this->initialize($context);
    }

    function it_validates_unique_value_from_form_data(
        $uniqueValuesSet,
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $uniqueValue,
        AttributeInterface $uniqueAttribute,
        ExecutionContextInterface $context,
        UniqueValue $constraint,
        Form $form,
        ProductInterface $product
    ) {
        $context->getRoot()->willReturn($form);
        $form->getData()->willReturn($product);
        $product->getValue('unique_attribute')->willReturn($uniqueValue);

        $uniqueValue->getAttribute()->willReturn($uniqueAttribute);
        $uniqueAttribute->isUnique()->willReturn(true);
        $uniqueValue->getProduct()->willReturn($product);

        $productRepository->valueExists($uniqueValue)->willReturn(false);
        $uniqueValuesSet->addValue($uniqueValue)->willReturn(true);

        $this->validate("my_value", $constraint)->shouldReturn(null);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
    }

    function it_adds_violation_with_non_unique_value_from_form_data_and_value_comes_from_database(
        $uniqueValuesSet,
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $uniqueValue,
        AttributeInterface $uniqueAttribute,
        ExecutionContextInterface $context,
        UniqueValue $constraint,
        ProductInterface $product,
        Form $form,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context->getRoot()->willReturn($form);
        $form->getData()->willReturn($product);
        $product->getValue('unique_attribute')->willReturn($uniqueValue);

        $uniqueValue->getAttribute()->willReturn($uniqueAttribute);
        $uniqueValue->getProduct()->willReturn($product);
        $uniqueAttribute->isUnique()->willReturn(true);
        $uniqueValue->getData()->willReturn('a content');
        $uniqueAttribute->getCode()->willReturn('unique_attribute');

        $productRepository->valueExists($uniqueValue)->willReturn(true);
        $uniqueValuesSet->addValue($uniqueValue)->willReturn(true);

        $context->buildViolation($constraint->message, Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate("my_value", $constraint)->shouldReturn(null);
    }

    function it_adds_violation_with_non_unique_value_from_form_data_and_value_comes_from_memory(
        $uniqueValuesSet,
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $uniqueValue,
        AttributeInterface $uniqueAttribute,
        ExecutionContextInterface $context,
        UniqueValue $constraint,
        ProductInterface $product,
        Form $form,
        ConstraintViolationBuilderInterface $violation
    ) {
        $context->getRoot()->willReturn($form);
        $form->getData()->willReturn($product);
        $product->getValue('unique_attribute')->willReturn($uniqueValue);

        $uniqueValue->getAttribute()->willReturn($uniqueAttribute);
        $uniqueValue->getProduct()->willReturn($product);
        $uniqueAttribute->isUnique()->willReturn(true);
        $uniqueValue->getData()->willReturn('a content');
        $uniqueAttribute->getCode()->willReturn('unique_attribute');

        $productRepository->valueExists($uniqueValue)->willReturn(false);
        $uniqueValuesSet->addValue($uniqueValue)->willReturn(false);

        $context->buildViolation($constraint->message, Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate("my_value", $constraint)->shouldReturn(null);
    }

    function it_does_not_validate_with_non_context(
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $value,
        ExecutionContextInterface $emptyContext,
        Constraint $constraint
    ) {
        $this->initialize($emptyContext);
        $emptyContext->getRoot()->willReturn(null);
        $productRepository->valueExists($value)->shouldNotBeCalled();
        $emptyContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate("my_value", $constraint)->shouldReturn(null);
    }

    function it_does_not_validate_with_empty_value(
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $value,
        ExecutionContextInterface $context,
        Constraint $constraint
    ) {
        $productRepository->valueExists($value)->shouldNotBeCalled();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate("", $constraint)->shouldReturn(null);
    }
}
