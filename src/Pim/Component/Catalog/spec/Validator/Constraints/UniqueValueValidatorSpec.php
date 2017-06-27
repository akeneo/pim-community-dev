<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface;
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
    const PROPERTY_PATH='children[values].children[unique_attribute].children[text].data';

    function let(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        ExecutionContextInterface $context,
        Form $form,
        ProductInterface $product,
        ValueInterface $value
    ) {
        $this->beConstructedWith($uniqueDataRepository, $uniqueValuesSet);

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

    function it_builds_a_violation_if_the_value_is_already_in_database_for_another_product(
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        $context,
        $uniqueDataRepository
    ) {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');

        $value->getAttribute()->willReturn($releaseDate);
        $value->__toString()->willReturn('2015-16-03');

        $uniqueDataRepository->uniqueDataExistsInAnotherProduct($value, $product)->willReturn(true);

        $context->buildViolation(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_builds_a_violation_if_the_value_has_already_been_validated_in_a_bulk(
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        $context,
        $uniqueValuesSet
    ) {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');

        $value->getAttribute()->willReturn($releaseDate);
        $value->__toString()->willReturn('2015-16-03');

        $uniqueValuesSet->addValue($value, $product)->willReturn(false);

        $context->buildViolation(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_skips_empty_objects(
        UniqueValue $constraint,
        $context,
        $uniqueDataRepository,
        $uniqueValuesSet
    ) {
        $uniqueValuesSet->addValue(Argument::any())->shouldNotBeCalled();
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct(Argument::cetera())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_skips_non_values_objects(
        \StdClass $object,
        UniqueValue $constraint,
        $context,
        $uniqueDataRepository,
        $uniqueValuesSet
    ) {
        $uniqueValuesSet->addValue(Argument::any())->shouldNotBeCalled();
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct(Argument::cetera())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_skips_non_unique_values(
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
        $context,
        $uniqueDataRepository,
        $uniqueValuesSet
    ) {
        $releaseDate->isUnique()->willReturn(false);

        $value->getAttribute()->willReturn($releaseDate);

        $uniqueValuesSet->addValue(Argument::any())->shouldNotBeCalled();
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct(Argument::cetera())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_add_a_violation_for_valid_values(
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
        ProductInterface $product,
        $context,
        $uniqueDataRepository,
        $uniqueValuesSet
    ) {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');

        $value->getAttribute()->willReturn($releaseDate);
        $value->__toString()->willReturn('2015-16-03');

        $uniqueValuesSet->addValue($value, $product)->willReturn(true);
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct($value, $product)->willReturn(false);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }
}
