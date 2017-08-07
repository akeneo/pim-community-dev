<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_convert_flat_product_model_to_standard_format(
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
            'identifier' => 'identifier',
            'parent' => '1234',
            'family' => 'family_variant',
            'categories' => 'tshirt,pull',
            'name-en_US' => 'name',
            'description-en_US-ecommerce' => 'description',
        ];

        $mappedFlatProductModel = [
            'identifier' => 'identifier',
            'parent' => '1234',
            'family_variant' => 'family_variant',
            'categories' => 'tshirt,pull',
            'name-en_US' => 'name',
            'description-en_US-ecommerce' => 'description',
        ];

        $columnsMapper->map($flatProductModel, [
            'family' => 'family_variant',
        ])->willReturn($mappedFlatProductModel);

        $columnsMerger->merge($mappedFlatProductModel)->willReturn($mappedFlatProductModel);

        $attributeColumnsResolver->resolveIdentifierField()->willReturn(['identifier']);
        $fieldsRequirementChecker->checkFieldsPresence($mappedFlatProductModel, 'identifier');

        $fieldConverter->supportsColumn('identifier')->willreturn(true);
        $fieldConverter->convert('identifier', 'identifier')->willreturn([$identifierConverter]);
        $identifierConverter->appendTo([])->willReturn(['identifier' => 'identifier']);

        $fieldConverter->supportsColumn('parent')->willreturn(1324);
        $fieldConverter->convert('parent', '1234')->willreturn([$parentConverter]);
        $parentConverter->appendTo(['identifier' => 'identifier'])->willReturn([
            'identifier' => 'identifier',
            'parent' => 1234,
        ]);

        $fieldConverter->supportsColumn('family_variant')->willreturn(true);
        $fieldConverter->convert('family_variant', 'family_variant')->willreturn([$familyVariantConverter]);
        $familyVariantConverter->appendTo([
            'identifier' => 'identifier',
            'parent' => 1234,
        ])->willReturn([
            'identifier' => 'identifier',
            'parent' => 1234,
            'family_variant' => 'family_variant'
        ]);

        $fieldConverter->supportsColumn('categories')->willreturn(true);
        $fieldConverter->convert('categories', 'tshirt,pull')->willreturn([$categoryConverter]);
        $categoryConverter->appendTo([
            'identifier' => 'identifier',
            'parent' => 1234,
            'family_variant' => 'family_variant'
        ])->willReturn([
            'identifier' => 'identifier',
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
            'identifier' => 'identifier',
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
}
