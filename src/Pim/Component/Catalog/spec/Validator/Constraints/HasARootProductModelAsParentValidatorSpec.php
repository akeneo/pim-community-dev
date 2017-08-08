<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Validator\Constraints\HasARootProductModelAsParent;
use Pim\Component\Catalog\Validator\Constraints\HasARootProductModelAsParentValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class HasARootProductModelAsParentValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HasARootProductModelAsParentValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldImplement(ConstraintValidator::class);
    }
    
    function it_adds_a_violation_if_the_parent_is_not_a_root_product_model(
        $context,
        ProductModelInterface $productModel,
        ProductModelInterface $rootProductModel,
        HasARootProductModelAsParent $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($rootProductModel);
        $rootProductModel->isRootProductModel()->willReturn(false);

        $context->buildViolation(HasARootProductModelAsParent::INVALID_PARENT)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $productModel->getVariationLevel()->willReturn(1);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getNumberOfLevel()->willReturn(1);

        $this->validate($productModel, $constraint);
    }

    function it_skips_the_root_product_model(
        $context,
        ProductModelInterface $productModel,
        HasARootProductModelAsParent $constraint
    ) {
        $productModel->isRootProductModel()->willReturn(true);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($productModel, $constraint);
    }

    function it_only_works_with_the_right_constraint(ProductModelInterface $productModel, Constraint $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$productModel, $constraint]);
    }

    function it_only_works_with_a_product_model(\StdClass $productModel, HasARootProductModelAsParent $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$productModel, $constraint]);
    }
}
