<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Export\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

abstract class AbstractProductModelExportTestCase extends TestCase
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

        $this->createProductModel('root_product_model_visible_for_redactor', [
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryA2'],
            'values' => [],
            'parent' => null
        ]);

        $this->createProductModel('root_product_model_visible_for_manager_only', [
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryB'],
            'values' => [],
            'parent' => null
        ]);

        $this->get('doctrine')->getManager()->clear();
        $this->jobLauncher->flushJobQueue();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $code
     * @param array  $data
     *
     * @return ProductModelInterface
     */
    protected function createProductModel(string $code, array $data = []) : ProductModelInterface
    {
        $productModel = new ProductModel();
        $productModel->setCode($code);
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }
}
