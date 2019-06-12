<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class FetchProductModelRowsFromCodesIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_fetch_product_models_from_codes()
    {
        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $fixturesLoader = new ProductGridFixturesLoader(
            static::$kernel->getContainer(),
            $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))
        );
        [$rootProductModel, $subProductModel] = $fixturesLoader->createProductAndProductModels()['product_models'];

        $query = $this->get('akeneo.pim.enrichment.product.grid.query.fetch_product_model_rows_from_codes');
        $rows = $query(['root_product_model', 'sub_product_model'], ['sku', 'an_image', 'a_text'], 'ecommerce', 'en_US', $userId);

        $akeneoImage = current($this
            ->get('akeneo_file_storage.repository.file_info')
            ->findAll($this->getFixturePath('akeneo.jpg')));


        $expectedRows = [
            Row::fromProductModel(
                'root_product_model',
                'A family A',
                $rootProductModel->getCreated(),
                $rootProductModel->getUpdated(),
                '[root_product_model]',
                MediaValue::value('an_image', $akeneoImage),
                $rootProductModel->getId(),
                ['total' => 1, 'complete' => 0],
                null,
                new ValueCollection([
                    MediaValue::value('an_image', $akeneoImage)
                ])
            ),
            Row::fromProductModel(
                'sub_product_model',
                'A family A',
                $subProductModel->getCreated(),
                $subProductModel->getUpdated(),
                '[sub_product_model]',
                MediaValue::value('an_image', $akeneoImage),
                $subProductModel->getId(),
                ['total' => 1, 'complete' => 0],
                'root_product_model',
                new ValueCollection([
                    MediaValue::value('an_image', $akeneoImage),
                    ScalarValue::value('a_text', 'a_text')
                ])
            ),
        ];

        AssertRows::same($expectedRows, $rows);
    }

    public function test_fetch_product_models_images()
    {
        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $fixturesLoader = new ProductGridFixturesLoader(
            static::$kernel->getContainer(),
            $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))
        );
        $rootProductModelWithLabelInProduct = $fixturesLoader->createProductModelsWithLabelInProduct();
        $rootProductModelWithLabelInSubProductModel = $fixturesLoader->createProductModelsWithLabelInSubProductModel();
        $subProductModelWithLabelInParent = $fixturesLoader->createProductModelsWithLabelInParentProductModel();

        $query = $this->get('akeneo.pim.enrichment.product.grid.query.fetch_product_model_rows_from_codes');
        $rows = $query(['root_product_model_without_sub_product_model', 'root_product_model_with_image_in_sub_product_model', 'sub_product_model'], [], 'ecommerce', 'en_US', $userId);

        $akeneoImage = current($this
            ->get('akeneo_file_storage.repository.file_info')
            ->findAll($this->getFixturePath('akeneo.jpg')));


        $expectedRows = [
            Row::fromProductModel(
                'root_product_model_without_sub_product_model',
                '[test_family]',
                $rootProductModelWithLabelInProduct->getCreated(),
                $rootProductModelWithLabelInProduct->getUpdated(),
                '[root_product_model_without_sub_product_model]',
                MediaValue::value('an_image', $akeneoImage),
                $rootProductModelWithLabelInProduct->getId(),
                ['total' => 1, 'complete' => 1],
                null,
                new ValueCollection([])
            ),
            Row::fromProductModel(
                'root_product_model_with_image_in_sub_product_model',
                '[test_family]',
                $rootProductModelWithLabelInSubProductModel->getCreated(),
                $rootProductModelWithLabelInSubProductModel->getUpdated(),
                '[root_product_model_with_image_in_sub_product_model]',
                MediaValue::value('an_image', $akeneoImage),
                $rootProductModelWithLabelInSubProductModel->getId(),
                ['total' => 0, 'complete' => 0],
                null,
                new ValueCollection([])
            ),
            Row::fromProductModel(
                'sub_product_model',
                '[test_family]',
                $subProductModelWithLabelInParent->getCreated(),
                $subProductModelWithLabelInParent->getUpdated(),
                '[sub_product_model]',
                MediaValue::value('an_image', $akeneoImage),
                $subProductModelWithLabelInParent->getId(),
                ['total' => 0, 'complete' => 0],
                'root_product_model_with_sub_product_model',
                new ValueCollection()
            ),
        ];

        AssertRows::same($expectedRows, $rows);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

