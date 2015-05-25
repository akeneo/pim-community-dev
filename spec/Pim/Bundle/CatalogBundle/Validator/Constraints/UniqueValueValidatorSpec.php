<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

class UniqueValueValidatorSpec extends ObjectBehavior
{
    const PROPERTY_PATH='children[values].children[unique_attribute].children[varchar].data';

    function let(
        ProductRepositoryInterface $productRepository,
        ExecutionContextInterface $context,
        Form $form,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $this->beConstructedWith($productRepository);

        $product->getValue('unique_attribute')->willReturn($value);

        $form->getData()->willReturn($product);

        $context->getPropertyPath()->willReturn(self::PROPERTY_PATH);
        $context->getRoot()->willReturn($form);

        $this->initialize($context);
    }

    function it_validates_unique_value_from_form_data(
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $uniqueValue,
        AttributeInterface $uniqueAttribute,
        ProductValueInterface $identifierValue,
        ExecutionContextInterface $context,
        UniqueValue $constraint,
        Form $form,
        ProductInterface $product
    ) {
        $context->getRoot()->willReturn($form);
        $form->getData()->willReturn($product);
        $product->getValue('unique_attribute')->willReturn($uniqueValue);

        $uniqueValue->getData()->willReturn('a content');
        $uniqueValue->getAttribute()->willReturn($uniqueAttribute);
        $uniqueValue->getProduct()->willReturn($product);
        $uniqueValue->getLocale()->willReturn(null);
        $uniqueValue->getScope()->willReturn(null);
        $uniqueAttribute->isUnique()->willReturn(true);
        $uniqueAttribute->getCode()->willReturn('unique_attribute');

        $product->getIdentifier()->willReturn($identifierValue);

        $productRepository->valueExists($uniqueValue)->willReturn(false);
        $this->validate("my_value", $constraint)->shouldReturn(null);
        $context->addViolation(Argument::any())->shouldNotBeCalled();
    }

    function it_adds_violation_with_non_unique_value_from_form_data(
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $uniqueValue,
        AttributeInterface $uniqueAttribute,
        ProductValueInterface $identifierValue,
        ExecutionContextInterface $context,
        UniqueValue $constraint,
        ProductInterface $product,
        Form $form
    ) {
        $context->getRoot()->willReturn($form);
        $form->getData()->willReturn($product);
        $product->getValue('unique_attribute')->willReturn($uniqueValue);

        $uniqueValue->getData()->willReturn('a content');
        $uniqueValue->getAttribute()->willReturn($uniqueAttribute);
        $uniqueValue->getProduct()->willReturn($product);
        $uniqueValue->getLocale()->willReturn(null);
        $uniqueValue->getScope()->willReturn(null);
        $uniqueAttribute->isUnique()->willReturn(true);
        $uniqueAttribute->getCode()->willReturn('unique_attribute');

        $product->getIdentifier()->willReturn($identifierValue);

        $productRepository->valueExists($uniqueValue)->willReturn(true);
        $context->addViolation($constraint->message, Argument::any())->shouldBeCalled();

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
        $emptyContext->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate("my_value", $constraint)->shouldReturn(null);
    }

    function it_does_not_validate_with_empty_value(
        ProductRepositoryInterface $productRepository,
        ProductValueInterface $value,
        ExecutionContextInterface $context,
        Constraint $constraint
    ) {
        $productRepository->valueExists($value)->shouldNotBeCalled();
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate("", $constraint)->shouldReturn(null);
    }
}
