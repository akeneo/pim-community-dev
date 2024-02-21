<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTree;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTreeValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductModelPositionInTheVariantTreeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelPositionInTheVariantTreeValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldImplement(ConstraintValidator::class);
    }

    function it_adds_a_violation_if_the_parent_is_not_a_root_product_model(
        $context,
        ProductModelInterface $productModel,
        ProductModelInterface $parentProductModel,
        ProductModelPositionInTheVariantTree $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($parentProductModel);
        $productModel->getCode()->willReturn('product_model');
        $parentProductModel->isRootProductModel()->willReturn(false);
        $parentProductModel->getCode()->willReturn('parent_product_model');

        $context->buildViolation(
            ProductModelPositionInTheVariantTree::INVALID_PARENT,
            [
                '%product_model%' => 'product_model',
                '%parent_product_model%' => 'parent_product_model',
            ]
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('parent')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(2);

        $this->validate($productModel, $constraint);
    }

    function it_adds_a_violation_if_the_product_model_has_a_parent_when_it_should_not(
        $context,
        ProductModelInterface $productModel,
        ProductModelInterface $parentProductModel,
        ProductModelPositionInTheVariantTree $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($parentProductModel);
        $productModel->getCode()->willReturn('product_model');
        $parentProductModel->isRootProductModel()->shouldNotBeCalled();
        $parentProductModel->getCode()->shouldNotBeCalled();

        $context->buildViolation(
            ProductModelPositionInTheVariantTree::CANNOT_HAVE_PARENT,
            [
                '%product_model%' => 'product_model',
            ]
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('parent')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $this->validate($productModel, $constraint);
    }

    function it_skips_the_root_product_model(
        $context,
        ProductModelInterface $productModel,
        ProductModelPositionInTheVariantTree $constraint
    ) {
        $productModel->isRootProductModel()->willReturn(true);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($productModel, $constraint);
    }

    function it_only_works_with_the_right_constraint(ProductModelInterface $productModel, Constraint $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$productModel, $constraint]);
    }

    function it_only_works_with_a_product_model(
        \StdClass $productModel,
        ProductModelPositionInTheVariantTree $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$productModel, $constraint]);
    }
}
