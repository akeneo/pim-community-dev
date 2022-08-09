<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Export\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

abstract class AbstractProductExportTestCase extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        $product = $this->createProduct('product_viewable_by_everybody_1', [
            'categories' => ['categoryA2'],
            'values'     => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'EN tablet', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'FR tablet', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ['data' => 'DE tablet', 'locale' => 'de_DE', 'scope' => 'tablet']
                ],
                'a_number_float' => [['data' => '12.05', 'locale' => null, 'scope' => null]],
                'a_localizable_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'de_DE', 'scope' => null]
                ],
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => -10, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_viewable_by_everybody_2', [
            'categories' => ['categoryA2', 'categoryB']
        ]);

        $this->createProduct('product_not_viewable_by_redactor', [
            'categories' => ['categoryB']
        ]);

        $this->createProduct('product_without_category', [
            'associations' => [
                'X_SELL' => ['products' => ['product_viewable_by_everybody_2', 'product_not_viewable_by_redactor']]
            ]
        ]);

        $this->get('doctrine')->getManager()->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }
}
