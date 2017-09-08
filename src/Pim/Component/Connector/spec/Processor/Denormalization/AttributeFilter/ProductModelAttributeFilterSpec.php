<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization\AttributeFilter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Connector\Processor\Denormalization\AttributeFilter\AttributeFilterInterface;
use Pim\Component\Connector\Processor\Denormalization\AttributeFilter\ProductModelAttributeFilter;
use Prophecy\Argument;

class ProductModelAttributeFilterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith($familyVariantRepository, $productModelRepository, ['code', 'parent', 'family_variant']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelAttributeFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_filters_the_attributes_for_a_root_product_model(
        $familyVariantRepository,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributeCollection
    ) {
        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributeCollection);
        $commonAttributeCollection->exists(Argument::any())->willReturn(true, false);

        $this->filter([
            'code' => 'code',
            'parent' => '',
            'family_variant' => 'family_variant',
            'values' => [
                'name-en_US' => 'name',
                'description-en_US-ecommerce' => 'description',
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => '',
            'family_variant' => 'family_variant',
            'values' => [
                'name-en_US' => 'name',
            ]
        ]);
    }

    function it_filters_the_attributes_for_a_sub_product_model(
        $familyVariantRepository,
        $productModelRepository,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $productModel,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $familyVariantAttribute
    ) {
        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $productModelRepository->findOneByIdentifier('parent')->willreturn($productModel);
        $productModel->getVariationLevel()->willReturn(1);
        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($familyVariantAttribute);
        $familyVariantAttribute->exists(Argument::any())->willReturn(false, true);

        $this->filter([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [
                'name-en_US' => 'name',
                'description-en_US-ecommerce' => 'description',
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [
                'description-en_US-ecommerce' => 'description',
            ]
        ]);
    }

    function it_skips_the_filtration_if_the_family_variant_is_invalid()
    {
        $this->filter([
            'code' => 'code',
        ])->shouldReturn([
            'code' => 'code',
        ]);
    }

    function it_skips_the_filtration_if_the_parent_does_not_exist(
        $familyVariantRepository,
        $productModelRepository,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $productModelRepository->findOneByIdentifier('parent')->willreturn(null);

        $this->filter([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [],
        ])->shouldReturn([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [],
        ]);
    }
}
