<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel;
use Pim\Component\Connector\Exception\DataArrayConversionException;
use Pim\Component\Connector\Exception\StructureArrayConversionException;

class ProductModelSpec extends ObjectBehavior
{
    function let(
        ColumnsMapper $columnsMapper,
        FieldConverter $fieldConverter,
        ArrayConverterInterface $productValueConverter,
        ColumnsMerger $columnsMerger,
        AttributeColumnsResolver $attributeColumnsResolver,
        FieldsRequirementChecker $fieldsRequirementChecker
    ) {
        $this->beConstructedWith(
            $columnsMapper,
            $fieldConverter,
            $productValueConverter,
            $columnsMerger,
            $attributeColumnsResolver,
            $fieldsRequirementChecker
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
        ];

        $columnsMapper->map($flatProductModel, [
            'family' => 'family_variant',
        ])->willReturn($flatProductModel);

        $columnsMerger->merge($flatProductModel)->willReturn($flatProductModel);

        $fieldsRequirementChecker->checkFieldsPresence($flatProductModel, ['code'])->shouldBeCalled();
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn(['name-en_US', 'description-en_US-ecommerce']);

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
            'family_variant' => 'family_variant'
        ]);

        $fieldConverter->supportsColumn('categories')->willreturn(true);
        $fieldConverter->convert('categories', 'tshirt,pull')->willreturn($categoryConverter);
        $categoryConverter->appendTo([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant'
        ])->willReturn([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant',
            'categories' => [
                'tshirt',
                'pull'
            ]
        ]);

        $fieldConverter->supportsColumn('name-en_US')->willreturn(false);
        $fieldConverter->supportsColumn('description-en_US-ecommerce')->willreturn(false);

        $productValueConverter->convert(["name-en_US" => "name", "description-en_US-ecommerce" => "description"])
            ->willReturn([
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
                ]
            ]
        );

        $this->convert($flatProductModel, [
            'mapping' => [
                'family' => 'family_variant',
            ]
        ])->shouldReturn([
            'code' => 'code',
            'parent' => 1234,
            'family_variant' => 'family_variant',
            'categories' => [
                'tshirt',
                'pull'
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
                ]
            ]
        ]);
    }

    function it_throws_an_exception_if_the_fields_are_not_scalar($attributeColumnsResolver)
    {
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([]);

        $this->shouldThrow(DataArrayConversionException::class)->during('convert', [[
            'code' => ['code'],
            'parent' => '1234',
            'family_variant' => 'family_variant',
            'categories' => 'tshirt,pull',
        ]]);
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

        $columnsMerger->merge($flatProductModel)->willReturn($flatProductModel);

        $fieldsRequirementChecker->checkFieldsPresence($flatProductModel, ['code'])->shouldBeCalled();
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn(['name', 'description-en_US-ecommerce']);

        $this->shouldThrow(StructureArrayConversionException::class)->during('convert', [
            $flatProductModel,
            [
                'mapping' => [
                    'family' => 'family_variant',
                ],
            ],
        ]);
    }
}
