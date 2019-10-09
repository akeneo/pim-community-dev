<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class VariantProductRatioIntegration extends TestCase
{
    /**
     * Test that the flat variant product completenesses for a SUB product model are good.
     */
    public function testCompletenessesForASubProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-braided-hat');
        $result = $this->get('pim_catalog.doctrine.query.find_variant_product_completeness')->findComplete($productModel);

        $this->assertEquals([
            'completenesses' => [
                'ecommerce' => [
                    'de_DE' => 0,
                    'en_US' => 2,
                    'fr_FR' => 1,
                ],
                'mobile' => [
                    'de_DE' => 0,
                    'en_US' => 2,
                    'fr_FR' => 1,
                ],
                'print' => [
                    'de_DE' => 0,
                    'en_US' => 2,
                    'fr_FR' => 1,
                ],
            ],
            'total' => 2
        ], $result->values());
    }

    /**
     * Test that the query does not fail if the product model does not have any child
     */
    public function testProductModelWithoutChild()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('minerva');
        $result = $this->get('pim_catalog.doctrine.query.find_variant_product_completeness')->findComplete($productModel);

        $this->assertEquals([
            'completenesses' => [],
            'total' => 0
        ], $result->values());
    }

    /**
     * Test that the flat variant product completenesses for a ROOT product model are good.
     */
    public function testCompletenessesForARootProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-biker-jacket');
        $result = $this->get('pim_catalog.doctrine.query.find_variant_product_completeness')->findComplete($productModel);

        $this->assertEquals([
            'completenesses' => [
                'ecommerce' => [
                    'de_DE' => 0,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
                'mobile' => [
                    'de_DE' => 0,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
                'print' => [
                    'de_DE' => 0,
                    'en_US' => 0,
                    'fr_FR' => 0,
                ],
            ],
            'total' => 6
        ], $result->values());
    }

    /**
     * This test filters the completeness by channel and locale
     */
    public function testCompletenesseDependingOnLocaleAndChannel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-braided-hat');
        $result = $this->get('pim_catalog.doctrine.query.find_variant_product_completeness')->findComplete(
            $productModel,
            'ecommerce',
            'en_US'
        );

        $this->assertEquals([
            'complete' => 2,
            'total' => 2
        ], $result->value('ecommerce', 'en_US'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
