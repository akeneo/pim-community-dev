<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\tests\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class FetchProductRowsFromIdentifiersIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_fetch_products_from_identifiers()
    {
        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id from oro_user where username = "admin"', [], 0);

        $fixturesLoader = new ProductGridFixturesLoader(
            static::$kernel->getContainer(),
            $this->getFixturePath('akeneo.jpg')
        );
        [$product1, $product2] = $fixturesLoader->createProductAndProductModels()['products'];

        $query = $this->get('akeneo.pim.enrichment.product.grid.query.fetch_product_rows_from_identifiers');
        $rows = $query(['baz', 'foo'], ['sku', 'a_localizable_image', 'a_scopable_image'], 'ecommerce', 'en_US', $userId);

        $sku = new Attribute();
        $sku->setCode('sku');

        $yesNo = new Attribute();
        $yesNo->setCode('a_yes_no');

        $anImage = new Attribute();
        $anImage->setCode('an_image');

        $aLocalizableImage = new Attribute();
        $aLocalizableImage->setCode('a_localizable_image');
        $aLocalizableImage->setLocalizable(true);

        $aScopableImage = new Attribute();
        $aScopableImage->setCode('a_scopable_image');
        $aScopableImage->setScopable(true);

        $akeneoImage = current($this
            ->get('akeneo_file_storage.repository.file_info')
            ->findAll($this->getFixturePath('akeneo.jpg')));

        $expectedRows = [
            Row::fromProduct(
                'foo',
                'A family A',
                ['[groupB]', '[groupA]'],
                true,
                $product1->getCreated(),
                $product1->getUpdated(),
                new ScalarValue($sku, null, null, 'foo'),
                new MediaValue($anImage, null, null, $akeneoImage),
                31,
                $product1->getId(),
                'sub_product_model',
                new ValueCollection([
                    new ScalarValue($sku, null, null, 'foo'),
                    new MediaValue($anImage, null, null, $akeneoImage)
                ])
            ),
            Row::fromProduct(
                'baz',
                null,
                [],
                true,
                $product2->getCreated(),
                $product2->getUpdated(),
                null,
                null,
                null,
                $product2->getId(),
                null,
                new ValueCollection([
                    new ScalarValue($sku, null, null, 'baz'),
                    new MediaValue($aLocalizableImage, null, 'en_US', $akeneoImage),
                    new MediaValue($aScopableImage, 'ecommerce', null, $akeneoImage),
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

