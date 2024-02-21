<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\SameFamilyThanParent;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\SameFamilyThanParentValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SameFamilyThanParentValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SameFamilyThanParentValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_validates_that_the_family_is_the_same_than_its_parent(
        ExecutionContextInterface $context,
        ProductInterface $variantProduct,
        SameFamilyThanParent $collaborator,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $productModelFamily,
        FamilyInterface $productFamily,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $this->initialize($context);

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getParent()->willReturn($productModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($productModelFamily);

        $variantProduct->getFamily()->willReturn($productFamily);

        $productFamily->getCode()->willReturn('code');
        $productFamily->getCode()->willReturn('other_code');

        $context->buildViolation(SameFamilyThanParent::MESSAGE)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('family')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($variantProduct, $collaborator);
    }

    function it_only_works_with_product_object(SameFamilyThanParent $constraint, \stdClass $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }

    function it_only_works_with_family_variant_axes_constraint(NotBlank $constraint, ProductInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }

    function it_does_not_build_violation_if_product_is_not_variant(
        ExecutionContextInterface $context,
        ProductInterface $product,
        SameFamilyThanParent $constraint
    ) {
        $this->initialize($context);

        $product->isVariant()->willReturn(false);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($product, $constraint);
    }
}
