<?php

namespace spec\Pim\Component\Catalog\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Validator\UniqueAxesCombinationSet;
use Prophecy\Argument;

class UniqueAxesCombinationSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueAxesCombinationSet::class);
    }

    function it_adds_axes_combinations(
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel,
        VariantProductInterface $variantProduct
    ) {
        $familyVariant->getCode()->willReturn('family_variant');

        $rootProductModel->getCode()->willReturn('root_product_model');
        $rootProductModel->getFamilyVariant()->willReturn($familyVariant);

        $productModel->getCode()->willReturn('product_model');
        $productModel->getParent()->willReturn($rootProductModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $variantProduct->getIdentifier()->willReturn('variant_product');
        $variantProduct->getParent()->willReturn($productModel);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $this->addCombination($productModel, '[a_color]')->shouldReturn(true);
        $this->addCombination($variantProduct, '[a_size]')->shouldReturn(true);
    }

    function it_does_not_add_axes_combinations_twice(
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel,
        ProductModelInterface $invalidProductModel
    ) {
        $familyVariant->getCode()->willReturn('family_variant');

        $rootProductModel->getCode()->willReturn('root_product_model');
        $rootProductModel->getFamilyVariant()->willReturn($familyVariant);

        $productModel->getCode()->willReturn('product_model');
        $productModel->getParent()->willReturn($rootProductModel);
        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $invalidProductModel->getCode()->willReturn('invalid_product_model');
        $invalidProductModel->getParent()->willReturn($rootProductModel);
        $invalidProductModel->getFamilyVariant()->willReturn($familyVariant);

        $this->addCombination($productModel, '[a_color]')->shouldReturn(true);
        $this->addCombination($invalidProductModel, '[a_color]')->shouldReturn(false);
    }
}
