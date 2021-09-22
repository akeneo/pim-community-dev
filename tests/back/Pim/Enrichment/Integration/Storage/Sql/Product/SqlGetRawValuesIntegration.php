<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlGetRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Query\PublicApi\GetRawValues;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetRawValuesIntegration extends TestCase
{
    private SqlGetRawValues $sqlGetRawValues;

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_product_identifiers_is_empty(): void
    {
        Assert::assertSame([], $this->getRawValues([]));
    }

    /**
     * @test
     */
    public function it_returns_raw_values_of_products(): void
    {
        $expected = [
            'watch' => [
                'ean' => [
                    '<all_channels>' => [
                        '<all_locales>' => '1234567890368',
                    ],
                ],
                'sku' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'watch',
                    ],
                ],
                'color' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'navy_blue',
                    ],
                ],
            ],
            '1111111179' => [
                'color' => ['<all_channels>' => ['<all_locales>' => 'blue']],
                'variation_name' => ['<all_channels>' => ['en_US' => 'Caelus blue']],
                'ean' => ['<all_channels>' => ['<all_locales>' => '1234567890191']],
                'sku' => ['<all_channels>' => ['<all_locales>' => '1111111179']],
                'size' => ['<all_channels>' => ['<all_locales>' => 'm']],
                'weight' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'unit' => 'GRAM',
                            'amount' => '800.0000',
                            'family' => 'Weight',
                            'base_data' => '0.800000000000',
                            'base_unit' => 'KILOGRAM',
                        ],
                    ],
                ],
                'name' => ['<all_channels>' => ['en_US' => 'Tuxedo with animal print']],
                'price' => ['<all_channels>' => ['<all_locales>' => [['amount' => '999.00', 'currency' => 'EUR']]]],
                'erp_name' => ['<all_channels>' => ['en_US' => 'Caelus']],
                'supplier' => ['<all_channels>' => ['<all_locales>' => 'mongo']],
                'collection' => ['<all_channels>' => ['<all_locales>' => ['summer_2016']]],
                'description' => ['ecommerce' => ['en_US' => "Get ready to party in this tuxedo with animal print grabbing composed of a single-breasted jacket with 2 buttons and matching pants. Our skinny suits offer a perfect contemporary snowstorm.\nThe model is 1.85 meters (6 feet 1 inch), wears a size 40 jacket and pants 32R.\nDry clean only."]],
                'wash_temperature' => ['<all_channels>' => ['<all_locales>' => '600']],
            ],
        ];

        Assert::assertEquals(
            $expected,
            $this->getRawValues(['watch', 'unknown', '1111111179'])
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlGetRawValues = $this->get(GetRawValues::class);
    }

    private function getRawValues(array $identifiers): array
    {
        $res = $this->sqlGetRawValues->fromProductIdentifiers($identifiers);

        return \is_array($res) ? $res : \iterator_to_array($res);
    }
}
