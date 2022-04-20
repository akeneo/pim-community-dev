<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Category\Domain\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntity;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntityValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueProductModelEntityValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType(UniqueProductModelEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_product_model_already_exist_in_the_database(
        $context,
        $objectRepository,
        UniqueValuesSet $uniqueValuesSet,
        ProductModelInterface $productModel,
        ProductModelInterface $productModelInDatabase,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueProductModelEntity();

        $productModel->getCode()->willReturn('code');
        $objectRepository->findOneByIdentifier('code')->willReturn($productModelInDatabase);
        $uniqueValuesSet->addValue(Argument::any(), $productModel)->willReturn(true);

        $productModelInDatabase->getId()->willReturn(40);
        $productModel->getId()->willReturn(64);

        $context->buildViolation('The same identifier is already set on another product model')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('code')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->ShouldBeCalled();

        $this->validate($productModel, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_to_the_context_if_a_product_model_already_exist_in_the_batch(
        $context,
        UniqueValuesSet $uniqueValuesSet,
        ProductModelInterface $productModel,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueProductModelEntity();

        $productModel->getCode()->willReturn('code');

        $uniqueValuesSet->addValue(Argument::any(), $productModel)->willReturn(false);

        $context->buildViolation('The same identifier is already set on another product model')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('code')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->ShouldBeCalled();

        $this->validate($productModel, $constraint)->shouldReturn(null);
    }

    function it_does_nothing_if_the_product_model_does_not_exist_in_database(
        $context,
        $objectRepository,
        UniqueValuesSet $uniqueValuesSet,
        ProductModelInterface $productModel
    ) {
        $constraint = new UniqueProductModelEntity();

        $productModel->getCode()->willReturn('code');
        $objectRepository->findOneByIdentifier('code')->willReturn(null);
        $uniqueValuesSet->addValue(Argument::any(), $productModel)->willReturn(true);

        $context->buildViolation('The same identifier is already set on another product')->shouldNotBeCalled();

        $this->validate($productModel, $constraint)->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_excepted_entity_is_not_a_product_model(
        CategoryInterface $category
    ) {
        $constraint = new UniqueProductModelEntity();

        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$category, $constraint]);
    }

    function it_throws_an_exception_if_the_excepted_constraint_is_not_a_unique_product_model_constraint(
        ProductModelInterface $productModel,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$productModel, $constraint]);
    }
}
