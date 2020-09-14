<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetProductModelLabelsIntegration extends TestCase
{
    public function test_that_it_returns_product_model_labels()
    {
        $result = $this->getProductModelLabels()->byCodesAndLocaleAndScope(['model-braided-hat', 'amor', 'dionysos', 'unknown'], 'fr_FR', 'ecommerce');
        $expected = [
            'model-braided-hat' => 'Chapeau tressé',
            'dionysos' => null,
            'amor' => null
        ];

        Assert::assertEqualsCanonicalizing($expected, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function getProductModelLabels(): GetProductModelLabelsInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_model_labels');
    }
}
