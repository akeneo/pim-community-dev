<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\tests\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;

class FetchProductModelRowsFromCodesIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_fetch_product_models_from_codes()
    {
        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id from oro_user where username = "admin"', [], 0);

        $fixturesLoader = new ProductGridFixturesLoader(
            static::$kernel->getContainer(),
            $this->getFixturePath('akeneo.jpg')
        );
        [$rootProductModel, $subProductModel] = $fixturesLoader->createProductAndProductModels()['product_models'];

        $query = $this->get('akeneo.pim.enrichment.product.grid.query.fetch_product_model_rows_from_codes');
        $rows = $query(['root_product_model', 'sub_product_model'], ['sku', 'an_image', 'a_text'], 'ecommerce', 'en_US', $userId);

        $aText = new Attribute();
        $aText->setCode('a_text');

        $anImage = new Attribute();
        $anImage->setCode('an_image');

        $akeneoImage = current($this
            ->get('akeneo_file_storage.repository.file_info')
            ->findAll($this->getFixturePath('akeneo.jpg')));


        $expectedRows = [
            Row::fromProductModel(
                'root_product_model',
                'A family A',
                $rootProductModel->getCreated(),
                $rootProductModel->getUpdated(),
                null,
                new MediaValue($anImage, null, null, $akeneoImage),
                $rootProductModel->getId(),
                ['total' => 1, 'complete' => 0],
                null,
                new ValueCollection([
                    new MediaValue($anImage, null, null, $akeneoImage)
                ])
            ),
            Row::fromProductModel(
                'sub_product_model',
                'A family A',
                $subProductModel->getCreated(),
                $subProductModel->getUpdated(),
                null,
                new MediaValue($anImage, null, null, $akeneoImage),
                $subProductModel->getId(),
                ['total' => 1, 'complete' => 0],
                'root_product_model',
                new ValueCollection([
                    new MediaValue($anImage, null, null, $akeneoImage),
                    new ScalarValue($aText, null, null, 'a_text')
                ])
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

