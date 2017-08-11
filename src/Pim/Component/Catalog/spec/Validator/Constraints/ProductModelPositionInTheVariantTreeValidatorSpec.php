<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Validator\Constraints\ProductModelPositionInTheVariantTree;
use Pim\Component\Catalog\Validator\Constraints\ProductModelPositionInTheVariantTreeValidator;
use PhpSpec\ObjectBehavior;
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
        ProductModelInterface $rootProductModel,
        ProductModelPositionInTheVariantTree $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($rootProductModel);
        $rootProductModel->isRootProductModel()->willReturn(false);

        $context->buildViolation(ProductModelPositionInTheVariantTree::INVALID_PARENT)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $productModel->getVariationLevel()->willReturn(1);
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

    function it_only_works_with_a_product_model(\StdClass $productModel, ProductModelPositionInTheVariantTree $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$productModel, $constraint]);
    }
}
