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
        Assert::assertArrayNotHasKey('unknown', $actual);

        $expected = [
            'braided-hat-m' => 'Braided hat ',
            '1111111292' => 'Scarf',
            'watch' => null,
        ];
        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_gets_product_labels_from_uuids(): void
    {
        $uuids = \array_map(
            fn (string $identifier): string => $this->getProductUuid($identifier)?->toString(),
            ['braided-hat-m', '1111111292', 'watch'],
        );
        $uuids[] = 'f412f686-892f-493b-9f76-9681e7d34b76'; // non existing product uuid

        $actual = $this->getProductLabelsQuery()->byUuidsAndLocaleAndScope(
            $uuids,
            'en_US',
            'ecommerce'
        );
        Assert::assertArrayNotHasKey('f412f686-892f-493b-9f76-9681e7d34b76', $actual);
        $expected = [
            $uuids[0] => 'Braided hat ',
            $uuids[1] => 'Scarf',
            $uuids[2] => null,
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
