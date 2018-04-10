<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Validator\Constraints\VariantProductParent;
use Pim\Component\Catalog\Validator\Constraints\VariantProductParentValidator;
use Pim\Component\Catalog\Validator\Constraints\ProductModelPositionInTheVariantTree;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VariantProductParentValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VariantProductParentValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_if_validated_entity_is_not_a_variant_product(
        ProductInterface $product,
        VariantProductParent $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            $product,
            $constraint
        ]);
    }

    function it_throws_an_exception_if_variant_product_is_not_validated_against_the_right_constraint(
        VariantProductInterface $product,
        ProductModelPositionInTheVariantTree $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            $product,
            $constraint
        ]);
    }

    function it_builds_violation_if_variant_product_has_no_parent(
        $context,
        VariantProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $productModel,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        VariantProductParent $constraint
    ) {
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $variantProduct->getParent()->willReturn(null);
        $variantProduct->getIdentifier()->willReturn('variant_product');

        $productModel->getVariationLevel()->shouldNotBeCalled();


        $context->buildViolation(VariantProductParent::NO_PARENT, [
            '%variant_product%' => 'variant_product',
        ])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('parent')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($variantProduct, $constraint);
    }

    function it_builds_violation_if_variant_product_parent_is_not_at_the_correct_tree_position(
        $context,
        VariantProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $productModel,
        Collection $productModels,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        VariantProductParent $constraint
    ) {
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $variantProduct->getParent()->willReturn($productModel);
        $variantProduct->getIdentifier()->willReturn('variant_product');

        $familyVariant->getNumberOfLevel()->willReturn(2);
        $productModel->getVariationLevel()->willReturn(0);
        $productModels->isEmpty()->willReturn(false);
        $productModel->getCode()->willReturn('product_model');

        $context->buildViolation(VariantProductParent::INVALID_PARENT, [
            '%variant_product%' => 'variant_product',
            '%product_model%' => 'product_model',
        ])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('parent')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($variantProduct, $constraint);
    }

    function it_does_not_build_violation_if_variant_product_parent_is_at_the_correct_tree_position(
        $context,
        VariantProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $productModel,
        Collection $productModels,
        VariantProductParent $constraint
    ) {
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $variantProduct->getParent()->willReturn($productModel);

        $familyVariant->getNumberOfLevel()->willReturn(2);
        $productModel->getVariationLevel()->willReturn(1);
        $productModels->isEmpty()->willReturn(true);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($variantProduct, $constraint);
    }
}
