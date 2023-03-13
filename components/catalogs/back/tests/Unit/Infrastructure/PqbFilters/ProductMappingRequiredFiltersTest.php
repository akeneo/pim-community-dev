<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\PqbFilters;

use Akeneo\Catalogs\Application\Exception\ProductMappingRequiredSourceMissingException;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\PqbFilters\ProductMappingRequiredFilters;
use PHPUnit\Framework\TestCase;

class ProductMappingRequiredFiltersTest extends TestCase
{
    /**
     * @dataProvider productMappingRequiredFiltersProvider
     */
    public function testItGetsProductMappingRequiredPQBFilters(
        array $productMapping,
        array $productMappingSchema,
        array $expectedPQBFilters,
    ): void {
        $this->assertEquals(
            $expectedPQBFilters,
            ProductMappingRequiredFilters::toPQBFilters($productMapping, $productMappingSchema),
        );
    }

    public function testItThrowWhenMappingRequiredSourceAreNotDefined(): void
    {
        $productMapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'title' => [
                'source' => null,
                'scope' => null,
                'locale' => null,
            ],
        ];

        $this->expectException(ProductMappingRequiredSourceMissingException::class);

        ProductMappingRequiredFilters::toPQBFilters($productMapping, $this->getProductMappingSchemaWithTitleRequired());
    }

    public function productMappingRequiredFiltersProvider(): array
    {
        return [
            'no required attribute' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                [],
                [],
            ],
            'one required field with no scope no locale' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                $this->getProductMappingSchemaWithTitleRequired(),
                [
                    [
                        'field' => 'name',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                    ],
                ],
            ],
            'one required field with scope no locale' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                ],
                $this->getProductMappingSchemaWithTitleRequired(),
                [
                    [
                        'field' => 'name',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                        'context' => [
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
            'one required field no scope locale' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => 'fr_FR',
                    ],
                ],
                $this->getProductMappingSchemaWithTitleRequired(),
                [
                    [
                        'field' => 'name',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                        'context' => [
                            'locale' => 'fr_FR',
                        ],
                    ],
                ],
            ],
            'one required field scope locale' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => 'ecommerce',
                        'locale' => 'fr_FR',
                    ],
                ],
                $this->getProductMappingSchemaWithTitleRequired(),
                [
                    [
                        'field' => 'name',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                        'context' => [
                            'locale' => 'fr_FR',
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
            'two required field with no scope no locale' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'short_description' => [
                        'source' => 'description',
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
                $this->getProductMappingSchemaWithTitleAndDescriptionRequired(),
                [
                    [
                        'field' => 'name',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                    ],
                    [
                        'field' => 'description',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                    ],
                ],
            ],
            'two required field with mixed scope locale' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => 'fr_FR',
                    ],
                    'short_description' => [
                        'source' => 'description',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                ],
                $this->getProductMappingSchemaWithTitleAndDescriptionRequired(),
                [
                    [
                        'field' => 'name',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                        'context' => [
                            'locale' => 'fr_FR',
                        ],
                    ],
                    [
                        'field' => 'description',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                        'context' => [
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
            'two required field but one with default value' => [
                [
                    'uuid' => [
                        'source' => 'uuid',
                        'scope' => null,
                        'locale' => null,
                    ],
                    'title' => [
                        'source' => 'name',
                        'scope' => null,
                        'locale' => 'fr_FR',
                        'default' => 'Default title',
                    ],
                    'short_description' => [
                        'source' => 'description',
                        'scope' => 'ecommerce',
                        'locale' => null,
                    ],
                ],
                $this->getProductMappingSchemaWithTitleAndDescriptionRequired(),
                [
                    [
                        'field' => 'description',
                        'operator' => Operator::IS_NOT_EMPTY,
                        'value' => '',
                        'context' => [
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getProductMappingSchemaWithTitleRequired(): array
    {
        return \json_decode(<<<'JSON_WRAP'
        {
           "$id":"https://example.com/product",
           "$schema":"https://api.akeneo.com/mapping/product/0.0.10/schema",
           "$comment":"My first schema !",
           "title":"Product Mapping",
           "description":"JSON Schema describing the structure of products expected by our application",
           "type":"object",
           "properties":{
              "uuid":{
                 "type":"string"
              },
              "title":{
                 "type":"string"
              },
              "short_description":{
                 "type":"string"
              }
           },
           "required":[
              "title"
           ]
        }
        JSON_WRAP, true);
    }
    private function getProductMappingSchemaWithTitleAndDescriptionRequired(): array
    {
        return \json_decode(<<<'JSON_WRAP'
        {
           "$id":"https://example.com/product",
           "$schema":"https://api.akeneo.com/mapping/product/0.0.10/schema",
           "$comment":"My first schema !",
           "title":"Product Mapping",
           "description":"JSON Schema describing the structure of products expected by our application",
           "type":"object",
           "properties":{
              "uuid":{
                 "type":"string"
              },
              "title":{
                 "type":"string"
              },
              "short_description":{
                 "type":"string"
              }
           },
           "required":[
              "title",
              "short_description"
           ]
        }
        JSON_WRAP, true);
    }
}
