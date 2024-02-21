<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntityValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueProductEntityValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        FindId $findId,
        AttributeRepositoryInterface $attributeRepository,
        UniqueValuesSet $uniqueValuesSet
    ) {
        $this->beConstructedWith($findId, $uniqueValuesSet, $attributeRepository);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueProductEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_product_already_exists_in_the_database(
        ExecutionContextInterface $context,
        FindId $findId,
        UniqueValuesSet $uniqueValuesSet,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        WriteValueCollection $values,
        ValueInterface $identifierValue,
        AttributeInterface $identifierAttribute,
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $uniqueValuesSet->addValue($identifierValue, $product)->willReturn(true);
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('identifier');
        $values->getByCodes('identifier')->willReturn($identifierValue);

        $product->getIdentifier()->willReturn('identifier');
        $uuid = Uuid::uuid4();
        $findId->fromIdentifier('identifier')->willReturn($uuid);
        $product->getUuid()->willReturn(Uuid::uuid4());

        $context->buildViolation(Argument::type('string'), Argument::type('array'))
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(UniqueProductEntity::UNIQUE_PRODUCT_ENTITY)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($product, $constraint);
    }

    function it_adds_violation_to_the_context_if_a_product_has_already_been_processed_in_the_batch(
        ExecutionContextInterface $context,
        UniqueValuesSet $uniqueValuesSet,
        AttributeRepositoryInterface $attributeRepository,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        WriteValueCollection $values,
        ValueInterface $identifierValue,
        AttributeInterface $identifierAttribute
    ) {
        $constraint = new UniqueProductEntity();
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('identifier');
        $values->getByCodes('identifier')->willReturn($identifierValue);
        $product->getValues()->willReturn($values);

        $uniqueValuesSet->addValue($identifierValue, $product)->willReturn(false);
        $context->buildViolation(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setCode(UniqueProductEntity::UNIQUE_PRODUCT_ENTITY)
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($product, $constraint);
    }

    function it_does_nothing_if_the_product_does_not_exist_in_database(
        ExecutionContextInterface $context,
        FindId $findId,
        UniqueValuesSet $uniqueValuesSet,
        ProductInterface $product,
        WriteValueCollection $values,
        ValueInterface $identifierValue,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $mainIdentifierAttribute
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($values);
        $values->isEmpty()->willReturn(false);
        $values->first()->willReturn($identifierValue);
        $uniqueValuesSet->addValue($identifierValue, $product)->willReturn(true);

        $attributeRepository->getIdentifier()->willReturn($mainIdentifierAttribute);
        $mainIdentifierAttribute->getCode()->willReturn('sku');
        $values->getByCodes('sku')->willReturn($identifierValue);

        $product->getIdentifier()->willReturn('identifier');
        $findId->fromIdentifier('identifier')->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($product, $constraint);
    }

    function it_does_nothing_if_the_product_does_not_have_an_identifier(
        ProductInterface $product,
        WriteValueCollection $values,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $mainIdentifierAttribute
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($values);
        $values->isEmpty()->willReturn(true);
        $attributeRepository->getIdentifier()->willReturn($mainIdentifierAttribute);
        $mainIdentifierAttribute->getCode()->willReturn('sku');
        $values->getByCodes('sku')->willReturn(null);

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_excepted_entity_is_not_a_product(
        CategoryInterface $category
    ) {
        $constraint = new UniqueProductEntity();

        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$category, $constraint]);
    }

    function it_throws_an_exception_if_the_excepted_constraint_is_not_a_unique_product_constraint(
        ProductInterface $product,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }
}
