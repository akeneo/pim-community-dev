<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\FetchProductModelRowsFromCodes;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

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

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.product_grid_fixtures_loader');
        $imagePath = $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'));
        [$rootProductModel, $subProductModel] = $fixturesLoader->createProductAndProductModels($imagePath)['product_models'];

        $query = $this->getFetchProductRowsFromCodes();
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
                new WriteValueCollection([
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
                new WriteValueCollection([
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

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.product_grid_fixtures_loader');
        $imagePath = $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'));

        $rootProductModelWithLabelInProduct = $fixturesLoader->createProductModelsWithLabelInProduct($imagePath);
        $rootProductModelWithLabelInSubProductModel = $fixturesLoader->createProductModelsWithLabelInSubProductModel($imagePath);
        $subProductModelWithLabelInParent = $fixturesLoader->createProductModelsWithLabelInParentProductModel($imagePath);

        $query = $this->getFetchProductRowsFromCodes();
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
                new WriteValueCollection([])
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
                new WriteValueCollection([])
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
                new WriteValueCollection()
            ),
        ];

        AssertRows::sameButOrderNotGuaranteed($expectedRows, $rows);
    }

    public function test_it_works_with_empty_product_model()
    {
        $this->createFamily('family', ['a_simple_select', 'a_yes_no']);
        $this->createFamilyVariant('family', 'familyVariant', ['a_simple_select'], ['a_yes_no']);
        $this->createProductModel('productModel', 'familyVariant');
        $this->createProductModel('subProductModel', 'familyVariant', 'productModel');
        $this->createVariantProduct('productVariant', 'subProductModel');

        $query = $this->getFetchProductRowsFromCodes();
        $result = $query(['subProductModel'], ['a_simple_select', 'a_yes_no'], 'ecommerce', 'en_US');

        Assert::count($result, 1);
        $row = $result[0];
        $simpleSelectValue = $row->values()->getValues()[0];
        Assert::same($simpleSelectValue->getAttributeCode(), 'a_simple_select');
        Assert::same($simpleSelectValue->getData(), 'optionA');
    }

    private function createFamily(string $familyCode, array $attributeCodes): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => array_merge(['sku'], $attributeCodes),
        ];

        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $familyData);
        $errors = $this->get('validator')->validate($family);
        Assert::same(0, $errors->count());
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(string $familyCode, string $variantCode, array $attributeCodesLvl1, array $attributeCodesLvl2): void
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => $variantCode,
            'family' => $familyCode,
            'variant_attribute_sets' => [
                [
                    'axes' => $attributeCodesLvl1,
                    'attributes' => $attributeCodesLvl1,
                    'level' => 1,
                ],
                [
                    'axes' => $attributeCodesLvl2,
                    'attributes' => $attributeCodesLvl2,
                    'level' => 2,
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        Assert::same(0, $errors->count());
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    private function createProductModel(
        string $productModelCode,
        string $familyVariantCode,
        ?string $parentCode = null
    ): void {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $data = [
            'code' => $productModelCode,
            'family_variant' => $familyVariantCode,
            'values' => [],
        ];
        if (null !== $parentCode) {
            $data['parent'] = $parentCode;
            $data['values'] = [
                'a_simple_select' => [
                    ['data' => 'optionA', 'locale' => null, 'scope' => null],
                ]
            ];
        }
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('validator')->validate($productModel);
        Assert::same(0, $errors->count());
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function createVariantProduct(string $identifier, string $parentCode): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        $this->get('pim_catalog.updater.product')->update($product, [
            'parent' => $parentCode,
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);
        $errors = $this->get('validator')->validate($product);
        Assert::same(0, $errors->count());
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getFetchProductRowsFromCodes(): FetchProductModelRowsFromCodes
    {
        return $this->get('akeneo.pim.enrichment.product.grid.query.fetch_product_model_rows_from_codes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
