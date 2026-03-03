<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMapper;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMerger;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;

class ProductModelSpec extends ObjectBehavior
{
    function let(
        ColumnsMapper $columnsMapper,
        FieldConverter $fieldConverter,
        ArrayConverterInterface $productValueConverter,
        ColumnsMerger $columnsMerger,
        AttributeColumnsResolver $attributeColumnsResolver,
        FieldsRequirementChecker $fieldsRequirementChecker,
        AssociationColumnsResolver $assocColumnsResolver
    ) {
        $assocColumnsResolver->resolveAssociationColumns()
            ->willReturn(['XSELL-products', 'XSELL-product-models', 'XSELL-groups']);
        $assocColumnsResolver->resolveQuantifiedAssociationColumns()
            ->willReturn(['PRODUCTSET-products', 'PRODUCTSET-product-models', 'PRODUCTSET-products-quantity', 'PRODUCTSET-product-models-quantity']);
        $this->beConstructedWith(
            $columnsMapper,
            $fieldConverter,
            $productValueConverter,
            $columnsMerger,
            $attributeColumnsResolver,
            $fieldsRequirementChecker,
            $assocColumnsResolver
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModel::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_flat_product_model_to_standard_format(
        $columnsMapper,
        $fieldConverter,
        $productValueConverter,
        $columnsMerger,
        $fieldsRequirementChecker,
        $attributeColumnsResolver,
        ConvertedField $identifierConverter,
        ConvertedField $parentConverter,
        ConvertedField $familyVariantConverter,
        ConvertedField $categoryConverter
    ) {
        $flatProductModel = [
            'code' => 'code',
            'parent' => '1234',
            'family_variant' => 'family_variant',
            'categories' => 'tshirt,pull',
            'name-en_US' => 'name',
            'description-en_US-ecommerce' => 'description',
            123456 => 'numerical as field code',
            '0123456' => 'numerical with trailing zero as field code',
            '12.33' => 'string float as field code',
            '1234567' => 'numerical as field code',
        ];

        $columnsMapper->map($flatProductModel, [
            'family' => 'family_variant',
        ])->willReturn($flatProductModel);

        $columnsMerger->merge($flatProductModel, ['with_associations' => false, 'mapping' => ['family' => 'family_variant']])->willReturn($flatProductModel);

        $fieldsRequirementChecker->checkFieldsPresence($flatProductModel, ['code'])->shouldBeCalled();
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn(['name-en_US', 'description-en_US-ecommerce', '123456', '0123456', '12.33', '1234567']);

        $fieldConverter->supportsColumn('code')->willreturn(true);
        $fieldConverter->convert('code', 'code')->willreturn($identifierConverter);
        $identifierConverter->appendTo([])->willReturn(['code' => 'code']);

        $fieldConverter->supportsColumn('parent')->willreturn(1324);
        $fieldConverter->convert('parent', '1234')->willreturn($parentConverter);
        $parentConverter->appendTo(['code' => 'code'])->willReturn([
            'code' => 'code',
            'parent' => 1234,
        ]);

        $fieldConverter->supportsColumn('family_variant')->willreturn(true);
        $fieldConverter->convert('family_variant', 'family_variant')->willreturn($familyVariantConverter);
        $familyVariantConverter->appendTo([
            'code' => 'code',
            'parent' => 1234,
        ])->willReturn([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant',
        ]);

        $fieldConverter->supportsColumn('categories')->willreturn(true);
        $fieldConverter->convert('categories', 'tshirt,pull')->willreturn($categoryConverter);
        $categoryConverter->appendTo([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant',
        ])->willReturn([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant',
            'categories' => [
                'tshirt',
                'pull',
            ],
        ]);

        $fieldConverter->supportsColumn('123456')->willreturn(false);
        $fieldConverter->supportsColumn('0123456')->willreturn(false);
        $fieldConverter->supportsColumn('12.33')->willreturn(false);
        $fieldConverter->supportsColumn('1234567')->willreturn(false);

        $fieldConverter->supportsColumn('name-en_US')->willreturn(false);
        $fieldConverter->supportsColumn('description-en_US-ecommerce')->willreturn(false);

        $productValueConverter->convert([
            "name-en_US" => "name",
            "description-en_US-ecommerce" => "description",
            123456 => 'numerical as field code',
            '0123456' => 'numerical with trailing zero as field code',
            '12.33' => 'string float as field code',
            '1234567' => 'numerical as field code',
        ])->willReturn([
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'name',
                ],
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'description',
                ],
            ],
            123456 => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'numerical as field code',
                ],
            ],
            '0123456' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'numerical with trailing zero as field code',
                ],
            ],
            '12.33' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'string float as field code',
                ],
            ],
            '1234567' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'numerical as field code',
                ],
            ],
        ]);

        $this->convert($flatProductModel, [
            'mapping' => [
                'family' => 'family_variant',
            ],
            'with_associations' => false
        ])->shouldReturn([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant',
            'categories' => [
                'tshirt',
                'pull',
            ],
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'name',
                    ],
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'description',
                    ],
                ],
                123456 => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'numerical as field code',
                    ],
                ],
                '0123456' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'numerical with trailing zero as field code',
                    ],
                ],
                '12.33' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'string float as field code',
                    ],
                ],
                '1234567' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'numerical as field code',
                    ],
                ],
            ],
        ]);
    }

    function it_throws_an_exception_if_the_fields_are_not_scalar($attributeColumnsResolver)
    {
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([]);

        $this->shouldThrow(DataArrayConversionException::class)->during('convert', [
            [
                'code' => ['code'],
                'parent' => '1234',
                'family_variant' => 'family_variant',
                'categories' => 'tshirt,pull',
            ],
            ['with_associations' => false]
        ]);
    }

    function it_throws_an_exception_if_family_variant_is_different_from_the_parent(
        $columnsMapper,
        $columnsMerger,
        $fieldsRequirementChecker,
        $attributeColumnsResolver
    ) {
        $flatProductModel = [
            'code' => 'code',
            'parent' => '1234',
            'family' => 'family_variant',
            'categories' => 'tshirt,pull',
            'name-en_US' => 'name',
            'description-en_US-ecommerce' => 'description',
        ];

        $columnsMapper->map($flatProductModel, [
            'family' => 'family_variant',
        ])->willReturn($flatProductModel);

        $columnsMerger->merge($flatProductModel, ['with_associations' => false, 'mapping' => ['family' => 'family_variant']])->willReturn($flatProductModel);

        $fieldsRequirementChecker->checkFieldsPresence($flatProductModel, ['code'])->shouldBeCalled();
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn(['name', 'description-en_US-ecommerce']);

        $this->shouldThrow(StructureArrayConversionException::class)->during('convert', [
            $flatProductModel,
            [
                'mapping' => [
                    'family' => 'family_variant',
                ],
                'with_associations' => false
            ],
        ]);
    }
}
