<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints\Product;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntity;
use Pim\Component\Catalog\Validator\Constraints\Product\UniqueProductEntityValidator;
use Pim\Component\Catalog\Validator\UniqueValuesSet;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueProductEntityValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        IdentifiableObjectRepositoryInterface $objectRepository,
        UniqueValuesSet $uniqueValuesSet
    ) {
        $this->beConstructedWith($objectRepository, $uniqueValuesSet);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueProductEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_product_already_exists_in_the_database(
        $context,
        $objectRepository,
        $uniqueValuesSet,
        ProductInterface $product,
        ProductInterface $productInDatabase,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ValueCollectionInterface $values,
        ValueInterface $identifierValue
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($values);
        $values->isEmpty()->willReturn(false);
        $values->first()->willReturn($identifierValue);
        $uniqueValuesSet->addValue($identifierValue, $product)->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');
        $objectRepository->findOneByIdentifier('identifier')->willReturn($productInDatabase);

        $productInDatabase->getId()->willReturn(40);
        $product->getId()->willReturn(64);

        $context->buildViolation('The same identifier is already set on another product')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_to_the_context_if_a_product_has_already_been_processed_in_the_batch(
        $context,
        $objectRepository,
        $uniqueValuesSet,
        ProductInterface $product,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ValueCollectionInterface $values,
        ValueInterface $identifierValue
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($values);
        $values->isEmpty()->willReturn(false);
        $values->first()->willReturn($identifierValue);
        $uniqueValuesSet->addValue($identifierValue, $product)->willReturn(false);

        $product->getIdentifier()->willReturn('identifier');
        $objectRepository->findOneByIdentifier('identifier')->willReturn(null);

        $context->buildViolation('The same identifier is already set on another product')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('identifier')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_does_nothing_if_the_product_does_not_exist_in_database(
        $context,
        $objectRepository,
        UniqueValuesSet $uniqueValuesSet,
        ProductInterface $product,
        ValueCollectionInterface $values,
        ValueInterface $identifierValue
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($values);
        $values->isEmpty()->willReturn(false);
        $values->first()->willReturn($identifierValue);
        $uniqueValuesSet->addValue($identifierValue, $product)->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');
        $objectRepository->findOneByIdentifier('identifier')->willReturn(null);

        $context->buildViolation('The same identifier is already set on another product')->shouldNotBeCalled();

        $this->validate($product, $constraint)->shouldReturn(null);
    }

    function it_does_nothing_if_the_product_does_not_have_an_identifier(
        ProductInterface $product,
        ValueCollectionInterface $values
    ) {
        $constraint = new UniqueProductEntity();

        $product->getValues()->willReturn($values);
        $values->filter(Argument::any())->willReturn($values);
        $values->isEmpty()->willReturn(true);
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
