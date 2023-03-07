<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\PqbFilters;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\PqbFilters\ProductSelectionCriteria;
use PHPUnit\Framework\TestCase;

class ProductSelectionCriteriaTest extends TestCase
{
    /**
     * @dataProvider productSelectionCriteriaProvider
     */
    public function testItFormatsProductSelectionCriteriaIntoPQBFilters(
        array $productSelectionCriteria,
        array $expectedPQBFilters,
    ): void {
        $this->assertEquals(
            $expectedPQBFilters,
            ProductSelectionCriteria::toPQBFilters($productSelectionCriteria),
        );
    }

    public function productSelectionCriteriaProvider(): array
    {
        return [
            'empty value' => [
                [
                    [
                        'field' => 'release_date',
                        'operator' => Operator::IS_EMPTY,
                    ],
                ],
                [
                    [
                        'field' => 'release_date',
                        'operator' => 'EMPTY',
                    ],
                ],
            ],
            'no scope no locale' => [
                [
                    [
                        'field' => 'enabled',
                        'operator' => Operator::EQUALS,
                        'value' => false,
                    ],
                ],
                [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => false,
                    ],
                ],
            ],
            'scope no locale' => [
                [
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'scope' => 'ecommerce',
                    ],
                ],
                [
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                        'context' => [
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
            'no scope locale' => [
                [
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'locale' => 'fr_FR',
                    ],
                ],
                [
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                        'context' => [
                            'locale' => 'fr_FR',
                        ],
                    ],
                ],
            ],
            'scope locale' => [
                [
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'locale' => 'fr_FR',
                        'scope' => 'ecommerce',
                    ],
                ],
                [
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                        'context' => [
                            'locale' => 'fr_FR',
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
            'scope locale are null' => [
                [
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                [
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                    ],
                ],
            ],
            'all cases' => [
                [
                    [
                        'field' => 'release_date',
                        'operator' => Operator::IS_EMPTY,
                    ],
                    [
                        'field' => 'enabled',
                        'operator' => Operator::EQUALS,
                        'value' => false,
                    ],
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'scope' => 'ecommerce',
                    ],
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'locale' => 'fr_FR',
                    ],
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'locale' => null,
                        'scope' => null,
                    ],
                    [
                        'field' => 'description',
                        'operator' => Operator::EQUALS,
                        'value' => 'une description',
                        'locale' => 'fr_FR',
                        'scope' => 'ecommerce',
                    ],
                ],
                [
                    [
                        'field' => 'release_date',
                        'operator' => 'EMPTY',
                    ],
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => false,
                    ],
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                        'context' => [
                            'scope' => 'ecommerce',
                        ],
                    ],
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                        'context' => [
                            'locale' => 'fr_FR',
                        ],
                    ],
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                    ],
                    [
                        'field' => 'description',
                        'operator' => '=',
                        'value' => 'une description',
                        'context' => [
                            'locale' => 'fr_FR',
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ],
        ];
    }
}
