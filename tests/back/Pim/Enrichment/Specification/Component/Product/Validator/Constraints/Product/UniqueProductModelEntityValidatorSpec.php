<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntity;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntityValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
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
        FindId $findId,
        UniqueValuesSet $uniqueValuesSet
    ) {
        $this->beConstructedWith($findId, $uniqueValuesSet);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueProductModelEntityValidator::class);
    }

    function it_adds_violation_to_the_context_if_a_product_model_already_exists_in_the_database(
        ExecutionContextInterface $context,
        FindId $findId,
        UniqueValuesSet $uniqueValuesSet,
        ProductModelInterface $productModel,
        ProductModelInterface $productModelInDatabase,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueProductModelEntity();

        $productModel->getCode()->willReturn('code');
        $findId->fromIdentifier('code')->willReturn('40');
        $uniqueValuesSet->addValue(Argument::any(), $productModel)->willReturn(true);

        $productModel->getId()->willReturn(64);

        $context->buildViolation('The same identifier is already set on another product model')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('code')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($productModel, $constraint);
    }

    function it_adds_violation_to_the_context_if_a_product_model_already_exist_in_the_batch(
        ExecutionContextInterface $context,
        UniqueValuesSet $uniqueValuesSet,
        ProductModelInterface $productModel,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $constraint = new UniqueProductModelEntity();

        $productModel->getCode()->willReturn('code');

        $uniqueValuesSet->addValue(Argument::any(), $productModel)->willReturn(false);

        $context->buildViolation('The same identifier is already set on another product model')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('code')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($productModel, $constraint);
    }

    function it_does_nothing_if_the_product_model_does_not_exist_in_database(
        ExecutionContextInterface $context,
        FindId $findId,
        UniqueValuesSet $uniqueValuesSet,
        ProductModelInterface $productModel
    ) {
        $constraint = new UniqueProductModelEntity();

        $productModel->getCode()->willReturn('code');
        $findId->fromIdentifier('code')->willReturn(null);
        $uniqueValuesSet->addValue(Argument::any(), $productModel)->willReturn(true);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($productModel, $constraint);
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
