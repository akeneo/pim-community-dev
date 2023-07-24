<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueValueValidatorSpec extends ObjectBehavior
{
    const PROPERTY_PATH = 'children[values].children[unique_attribute].children[text].data';

    function let(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ProductInterface $product,
        ValueInterface $value,
    ): void {
        $product->getValue('unique_attribute')->willReturn($value);
        $context->getPropertyPath()->willReturn(self::PROPERTY_PATH);
        $context->getRoot()->willReturn($product);

        $this->beConstructedWith($uniqueDataRepository, $uniqueValuesSet, $attributeRepository);

        $this->initialize($context);
    }

    function it_is_a_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_builds_a_violation_if_the_value_is_already_in_database_for_another_product(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ProductInterface $product,
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
    ):void {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

        $value->getAttributeCode()->willReturn('release_date');
        $value->__toString()->willReturn('2015-16-03');

        $uniqueValuesSet->addValue($value, $product)->willReturn(true);
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct($value, $product)->willReturn(true);

        $context->buildViolation(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(UniqueValue::UNIQUE_VALUE)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_builds_a_violation_if_the_value_has_already_been_validated_in_a_bulk(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ProductInterface $product,
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
    ): void {
        $context->getRoot()->willReturn($product);
        $releaseDate->isUnique()->willReturn(true);
        $releaseDate->getCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

        $value->getAttributeCode()->willReturn('release_date');
        $value->__toString()->willReturn('2015-16-03');

        $uniqueDataRepository->uniqueDataExistsInAnotherProduct($value, $product)->willReturn(false);
        $uniqueValuesSet->addValue($value, $product)->willReturn(false);

        $context->buildViolation(Argument::cetera())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(UniqueValue::UNIQUE_VALUE)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_skips_empty_objects(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ProductInterface $product,
        ValueInterface $value,
        UniqueValue $constraint,
    ): void {
        $uniqueValuesSet->addValue(Argument::any())->shouldNotBeCalled();
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct(Argument::cetera())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(null, $constraint);
    }

    function it_skips_non_values_objects(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        ExecutionContextInterface $context,
        UniqueValue $constraint,
    ): void {
        $uniqueValuesSet->addValue(Argument::any())->shouldNotBeCalled();
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct(Argument::cetera())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), $constraint);
    }

    function it_skips_non_unique_values(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
    ): void {
        $releaseDate->isUnique()->willReturn(false);

        $value->getAttributeCode()->willReturn('release_date');
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);

        $uniqueValuesSet->addValue(Argument::any())->shouldNotBeCalled();
        $uniqueDataRepository->uniqueDataExistsInAnotherProduct(Argument::cetera())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_add_a_violation_for_valid_values(
        ProductUniqueDataRepositoryInterface $uniqueDataRepository,
        UniqueValuesSet $uniqueValuesSet,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        ProductInterface $product,
        ValueInterface $value,
        UniqueValue $constraint,
        AttributeInterface $releaseDate,
    ): void {
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
