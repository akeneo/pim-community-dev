<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
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
        ValueInterface $value,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($uniqueDataRepository, $uniqueValuesSet, $attributeRepository);

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
        $uniqueDataRepository,
        $attributeRepository
    ) {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

        $value->getAttributeCode()->willReturn('release_date');
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
        $uniqueValuesSet,
        $attributeRepository
    ) {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

        $value->getAttributeCode()->willReturn('release_date');
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
        $uniqueValuesSet,
        $attributeRepository
    ) {
        $releaseDate->isUnique()->willReturn(false);

        $value->getAttributeCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

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
        $uniqueValuesSet,
        $attributeRepository
    ) {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

        $value->getAttributeCode()->willReturn('release_date');
        $value->__toString()->willReturn('2015-16-03');

        $uniqueValuesSet->addValue($value, $product)->willReturn(true);
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct($value, $product)->willReturn(false);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }
}
