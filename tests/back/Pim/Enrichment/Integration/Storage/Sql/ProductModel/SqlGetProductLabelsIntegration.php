<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetProductLabelsIntegration extends TestCase
{

    public function test_that_it_returns_product_labels()
    {
        $actual = $this->getProductLabelsQuery()->byIdentifiersAndLocaleAndScope(
            [
                'braided-hat-m',// Variant product
                '1111111292',// Simple product
                'watch',// Product without label
                'unknown'
            ],
            'en_US',
            'ecommerce'
        );

        $expected = [
            'braided-hat-m' => 'Braided hat ',
            '1111111292' => 'Scarf',
            'watch' => null,
        ];
        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function getProductLabelsQuery(): GetProductLabelsInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_labels');
    }
}
