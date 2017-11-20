<?php

namespace PimEnterprise\Bundle\ConnectorBundle\tests\integration\Export\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;

abstract class AbstractProductExportTestCase extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);

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
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'de_DE', 'scope' => null]
                ],
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => -10, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProductDraft('mary', $product, [
            'values'     => [
                'a_number_float' => [['data' => '20.09', 'locale' => null, 'scope' => null]],
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
        return $this->catalog->useTechnicalCatalog();
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

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param string           $userName
     * @param ProductInterface $product
     * @param array            $changes
     *
     * @return ProductDraftInterface
     */
    protected function createProductDraft(
        string $userName,
        ProductInterface $product,
        array $changes
    ) : ProductDraftInterface {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        $productDraft = $this->get('pimee_workflow.builder.draft')->build($product, $userName);
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }
}
