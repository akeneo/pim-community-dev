<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetProductRawValuesByAttributeQuery;
use Akeneo\Test\Integration\TestCase;

class GetProductRawValuesByAttributeQueryIntegration extends TestCase
{
    public function test_it_returns_product_values_by_attribute()
    {
        $productId = $this->createProduct();

        $expectedResult = [
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'some text'
                ],
            ],
            'a_yes_no' => [
                '<all_channels>' => [
                    '<all_locales>' => true
                ],
            ],
        ];

        $result = $this
            ->get(GetProductRawValuesByAttributeQuery::class)
            ->execute($productId, ['a_text', 'a_yes_no']);

        $this->assertSame($expectedResult, $result);
    }

    public function test_it_returns_empty_array_if_product_do_not_exists()
    {
        $result = $this
            ->get(GetProductRawValuesByAttributeQuery::class)
            ->execute(new ProductId(1418), ['a_text', 'a_yes_no']);

        $this->assertSame([], $result);
    }

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_family')
            ->withFamily('familyA3')
            ->build();

        $data = [
            'values' => [
                'a_text' => [['scope' => null, 'locale' => null, 'data' => 'some text']],
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => true]],
            ]
        ];
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
