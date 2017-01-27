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
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
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

    function it_is_a_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }
}
