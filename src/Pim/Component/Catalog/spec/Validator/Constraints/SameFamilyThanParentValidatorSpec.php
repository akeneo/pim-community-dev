<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Validator\Constraints\SameFamilyThanParent;
use Pim\Component\Catalog\Validator\Constraints\SameFamilyThanParentValidator;
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
        VariantProductInterface $variantProduct,
        SameFamilyThanParent $collaborator,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $productModelFamily,
        FamilyInterface $productFamily,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $this->initialize($context);

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

    function it_only_works_with_variant_product_object(SameFamilyThanParent $constraint, ProductInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }

    function it_only_works_with_family_variant_axes_constraint(NotBlank $constraint, VariantProductInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }
}
