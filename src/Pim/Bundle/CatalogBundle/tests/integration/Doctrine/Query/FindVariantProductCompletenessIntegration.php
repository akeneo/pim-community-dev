<?php
declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class FindVariantProductCompletenessIntegration extends TestCase
{
    /**
     * Test that the flat variant product completenesses for a SUB product model are good.
     */
    public function testCompletenessesForASubProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-braided-hat');
        $result = ($this->get('pim_catalog.doctrine.query.find_variant_product_completeness'))($productModel);

        $this->assertEquals([
            'completenesses' => [
                'ecommerce' => [
                    'de_DE' => 0,
                    'en_US' => 2,
                    'fr_FR' => 0,
                ],
                'mobile' => [
                    'de_DE' => 0,
                    'en_US' => 2,
                    'fr_FR' => 0,
                ],
                'print' => [
                    'de_DE' => 0,
                    'en_US' => 2,
                    'fr_FR' => 0,
                ],
            ],
            'total' => 2
        ], $result->normalizedCompletenesses());
    }

    /**
     * Test that the flat variant product completenesses for a ROOT product model are good.
     */
    public function testCompletenessesForARootProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-biker-jacket');
        $result = ($this->get('pim_catalog.doctrine.query.find_variant_product_completeness'))($productModel);

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
        ], $result->normalizedCompletenesses());
    }

    /**
     * This test filters the completeness by channel and locale
     */
    public function testCompletenesseDependingOnLocaleAndChannel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-braided-hat');
        $result = ($this->get('pim_catalog.doctrine.query.find_variant_product_completeness'))(
            $productModel,
            'ecommerce',
            'en_US'
        );

        $this->assertEquals('2/2', $result->ratio('ecommerce', 'en_US'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
