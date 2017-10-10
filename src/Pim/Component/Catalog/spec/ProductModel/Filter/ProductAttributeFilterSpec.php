<?php

namespace spec\Pim\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\ProductModel\Filter\ProductAttributeFilter;
use Prophecy\Argument;

class ProductAttributeFilterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($productModelRepository, $familyRepository, $productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Pim\Component\Catalog\ProductModel\Filter\ProductAttributeFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(\Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface::class);
    }

    function it_filters_the_attributes_that_does_not_belong_the_family(
        $familyRepository,
        $productRepository,
        FamilyInterface $family,
        ProductInterface $product,
        Collection $attribute
    ) {
        $familyRepository->findOneByIdentifier('Summer Tshirt')->willReturn($family);
        $family->getAttributes()->willReturn($attribute);
        $attribute->exists(Argument::any())->shouldBeCalledTimes(3);

        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);

        $this->filter(
            [
                'identifier' => 'tshirt',
                'family' => 'Summer Tshirt',
                'values' => [
                    'sku' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'tshirt',
                        ],
                    ],
                    'name' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => 'My very awesome T-shirt',
                        ],
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'mobile',
                            'data' => 'My awesome description',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_filters_the_attributes_that_does_not_belong_to_a_family_variant(
        $productModelRepository,
        $productRepository,
        ProductModelInterface $productModel,
        ProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $variantAttributeSet,
        Collection $attribute
    ) {
        $productModelRepository->findOneByIdentifier('parent-code')->willReturn($productModel);
        $productModel->getFamilyVariant()->willreturn($familyVariant);
        $productModel->getVariationLevel()->willreturn(1);

        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);

        $familyVariant->getVariantAttributeSet(2)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn($attribute);
        $attribute->exists(Argument::any())->shouldBeCalledTimes(3);

        $this->filter(
            [
                'identifier' => 'tshirt',
                'parent' => 'parent-code',
                'values' => [
                    'sku' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'tshirt',
                        ],
                    ],
                    'name' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => 'My very awesome T-shirt',
                        ],
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'mobile',
                            'data' => 'My awesome description',
                        ],
                    ],
                ],
            ]
        );
    }
}
