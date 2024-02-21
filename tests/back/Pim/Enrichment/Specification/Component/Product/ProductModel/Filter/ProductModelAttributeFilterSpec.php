<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductModelAttributeFilter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelAttributeFilterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($familyVariantRepository, $productModelRepository, $attributeRepository, ['code', 'parent', 'family_variant']);
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
        $attributeRepository,
        FamilyVariantInterface $familyVariant,
        CommonAttributeCollection $commonAttributeCollection,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('123')->willReturn($attribute);

        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $familyVariant->getCommonAttributes()->willReturn($commonAttributeCollection);
        $commonAttributeCollection->exists(Argument::any())->willReturn(true, false);

        $this->filter([
            'code' => 'code',
            'parent' => '',
            'family_variant' => 'family_variant',
            'values' => [
                'name' => [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'name'
                ],
                'description' => [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'description'
                ],
                '123' => [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'Data for 123'
                ],
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => '',
            'family_variant' => 'family_variant',
            'values' => [
                'name' => [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'name'
                ],
            ]
        ]);
    }

    function it_filters_the_attributes_for_a_sub_product_model(
        $familyVariantRepository,
        $productModelRepository,
        $attributeRepository,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $productModel,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $familyVariantAttribute,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);

        $familyVariantRepository->findOneByIdentifier('family_variant')->willreturn($familyVariant);
        $productModelRepository->findOneByIdentifier('code')->willreturn($productModel);
        $productModel->getParent()->willReturn(null);
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
                'name' => [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'name'
                ],
                'description' => [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'description'
                ],
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [
                'description' => [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'description'
                ],
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
        $productModelRepository->findOneByIdentifier('code')->willreturn(null);
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

    function it_throws_an_exception_when_attribute_does_not_exist()
    {
        $data = [
            'code' => 'code',
            'parent' => 'parent',
            'family_variant' => 'family_variant',
            'values' => [
                'description' => [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'description'
                ],
            ],
        ];

        $this->shouldThrow(
            UnknownPropertyException::unknownProperty('description')
        )->during('filter', [$data]);
    }
}
