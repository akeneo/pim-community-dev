<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Comparator\ComparatorInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistryInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Updater\ProductModelUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        NormalizerInterface $valueNormalizer,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ComparatorRegistryInterface $comparatorRegistry
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $valuesUpdater,
            $familyVariantRepository,
            $productModelRepository,
            $valueNormalizer,
            $attributesProvider,
            $comparatorRegistry,
            ['categories'],
            ['code']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_product_model(
        $familyVariantRepository,
        $productModelRepository,
        $propertySetter,
        $valuesUpdater,
        ProductModelInterface $productModel,
        ProductModelInterface $parentProductModel,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getId()->willReturn(null);
        $productModel->getParent()->willReturn(null);
        $productModel->getFamilyVariant()->willReturn(null);

        $propertySetter->setData($productModel, 'categories', ['tshirt'])->shouldBeCalled();
        $productModel->setCode('product_model_code')->shouldBeCalled();
        $productModelRepository->findOneByIdentifier('product_model_parent')->willreturn($parentProductModel);
        $productModel->setParent($parentProductModel)->shouldBeCalled();

        $familyVariantRepository->findOneByIdentifier('clothing_color_size')->willreturn($familyVariant);
        $productModel->setFamilyVariant($familyVariant)->shouldBeCalled();

        $valuesUpdater->update($productModel, [
            'name' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt',
            ],
            'description' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt super beau',
            ],
        ], [])->shouldBeCalled();

        $this->update($productModel, [
            'code' => 'product_model_code',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
            'family_variant' => 'clothing_color_size',
            'parent' => 'product_model_parent'
        ])->shouldReturn($this);
    }

    function it_throws_an_exception_if_an_option_axis_value_is_updated(
        $valueNormalizer,
        $attributesProvider,
        $comparatorRegistry,
        ProductModelInterface $productModel,
        ComparatorInterface $comparator,
        ValueInterface $colorValue,
        AttributeInterface $color
    ) {
        $currentStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => 'blue',
        ];

        $newStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => 'red',
        ];

        $productModel->getId()->willReturn(42);

        $attributesProvider->getAxes($productModel)->willReturn([$color]);
        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);

        $productModel->getValue('color')->willReturn($colorValue);
        $valueNormalizer->normalize($colorValue, 'standard')->willReturn($currentStandardValue);

        $comparatorRegistry->getAttributeComparator(AttributeTypes::OPTION_SIMPLE_SELECT)->willReturn($comparator);
        $comparator->compare($newStandardValue, $currentStandardValue)->willReturn($newStandardValue);

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'values' => [
                'color' => [
                    $newStandardValue
                ],
            ],
        ]]);
    }

    function it_throws_an_exception_if_a_simple_reference_data_axis_value_is_updated(
        $valueNormalizer,
        $attributesProvider,
        $comparatorRegistry,
        ProductModelInterface $productModel,
        ComparatorInterface $comparator,
        ValueInterface $colorValue,
        AttributeInterface $color
    ) {
        $currentStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => 'blue',
        ];

        $newStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => 'red',
        ];

        $productModel->getId()->willReturn(42);

        $attributesProvider->getAxes($productModel)->willReturn([$color]);
        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT);

        $productModel->getValue('color')->willReturn($colorValue);
        $valueNormalizer->normalize($colorValue, 'standard')->willReturn($currentStandardValue);

        $comparatorRegistry
            ->getAttributeComparator(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT)
            ->willReturn($comparator);
        $comparator->compare($newStandardValue, $currentStandardValue)->willReturn($newStandardValue);

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'values' => [
                'color' => [
                    $newStandardValue
                ],
            ],
        ]]);
    }

    function it_throws_an_exception_if_an_boolean_axis_value_is_updated(
        $valueNormalizer,
        $attributesProvider,
        $comparatorRegistry,
        ProductModelInterface $productModel,
        ComparatorInterface $comparator,
        ValueInterface $booleanValue,
        AttributeInterface $boolean
    ) {
        $currentStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => true,
        ];

        $newStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => false,
        ];

        $productModel->getId()->willReturn(42);

        $attributesProvider->getAxes($productModel)->willReturn([$boolean]);
        $boolean->getCode()->willReturn('boolean');
        $boolean->getType()->willReturn(AttributeTypes::BOOLEAN);

        $productModel->getValue('boolean')->willReturn($booleanValue);
        $valueNormalizer->normalize($booleanValue, 'standard')->willReturn($currentStandardValue);

        $comparatorRegistry
            ->getAttributeComparator(AttributeTypes::BOOLEAN)
            ->willReturn($comparator);
        $comparator->compare($newStandardValue, $currentStandardValue)->willReturn($newStandardValue);

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'values' => [
                'boolean' => [
                    $newStandardValue,
                ],
            ],
        ]]);
    }

    function it_throws_an_exception_if_an_metric_axis_value_is_updated(
        $valueNormalizer,
        $attributesProvider,
        $comparatorRegistry,
        ProductModelInterface $productModel,
        ComparatorInterface $comparator,
        ValueInterface $sizeValue,
        AttributeInterface $size
    ) {
        $currentStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => [
                'amount' => '420',
                'unit' => 'GRAM',
            ],
        ];

        $newStandardValue = [
            'locale' => null,
            'scope' => null,
            'data' => [
                'amount' => '42',
                'unit' => 'GRAM',
            ],
        ];

        $productModel->getId()->willReturn(42);

        $attributesProvider->getAxes($productModel)->willReturn([$size]);
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::METRIC);

        $productModel->getValue('size')->willReturn($sizeValue);
        $valueNormalizer->normalize($sizeValue, 'standard')->willReturn($currentStandardValue);

        $comparatorRegistry
            ->getAttributeComparator(AttributeTypes::METRIC)
            ->willReturn($comparator);
        $comparator->compare($newStandardValue, $currentStandardValue)->willReturn($newStandardValue);

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => '42',
                            'unit' => 'GRAM',
                        ],
                    ],
                ],
            ],
        ]]);
    }

    function it_throws_an_exception_if_a_parent_is_set_to_a_root_product_model(ProductModelInterface $productModel)
    {
        $productModel->getId()->willReturn(42);
        $productModel->isRootProductModel()->willReturn(true);

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'parent' => 'parent'
        ]]);
    }

    function it_throws_an_exception_if_a_non_existing_parent_is_set_to_a_product_model(
        $productModelRepository,
        ProductModelInterface $productModel
    ) {
        $productModel->getId()->willReturn(null);
        $productModelRepository->findOneByIdentifier('wrong_code')->willreturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'parent' => 'wrong_code'
        ]]);
    }

    function it_throws_an_exception_if_the_parent_is_updated(
        ProductModelInterface $productModel,
        ProductModelInterface $parent
    ) {
        $productModel->getId()->willReturn(42);
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('parent');

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'parent' => 'new_parent'
        ]]);
    }

    function it_throws_an_exception_if_the_family_variant_code_is_invalid(
        $familyVariantRepository,
        ProductModelInterface $productModel
    ) {
        $familyVariantRepository->findOneByIdentifier('wrong_code')->willreturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'wrong_code'
        ]]);
    }

    function it_throws_an_exception_if_the_family_variant_is_updated(
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willreturn('family_variant');

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'new_family_variant'
        ]]);
    }

    function it_throws_an_exception_if_the_family_variant_is_different_from_the_parent(
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getParent()->willReturn($parent);
        $parent->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willreturn('family_variant');

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'new_family_variant'
        ]]);
    }

    function it_only_works_with_product_model(ProductInterface $product)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$product, [], []]);
    }
}
