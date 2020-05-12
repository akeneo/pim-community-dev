<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductModelCodesQueryInterface;
use Akeneo\Test\Integration\TestCase;

class FindNonExistingProductModelCodesQueryIntegration extends TestCase
{
    /** @var FindNonExistingProductModelCodesQueryInterface */
    private $findNonExistingProductModelCodesQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findNonExistingProductModelCodesQuery = $this->get(
            'akeneo.pim.enrichment.product.query.find_non_existing_product_model_codes_query'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_returns_the_product_model_codes_that_does_not_exists()
    {
        $existingProductModelCodes = [
            'product_1',
            'product_2',
            'product_3',
            'product_4',
            'product_5',
        ];

        foreach ($existingProductModelCodes as $productModelCode) {
            $this->createProductModel($productModelCode);
        }

        $lookupProductModelCodes = [
            'product_1',
            'product_2',
            'product_3',
            'product_does_not_exists',
        ];

        $actualNonExistingProductModelCodes = $this->findNonExistingProductModelCodesQuery->execute(
            $lookupProductModelCodes
        );
        $expectedNonExistingProductModelCodes = [
            'product_does_not_exists',
        ];

        self::assertEquals(
            $actualNonExistingProductModelCodes,
            $expectedNonExistingProductModelCodes
        );
    }

    private function createProductModel(string $productModelCode): void
    {
        $productModel = new ProductModel();
        $this->get('pim_catalog.updater.product_model')
            ->update(
                $productModel,
                [
                    'code' => $productModelCode,
                    'parent' => null,
                    'family_variant' => 'familyVariantA1',
                ]
            );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }
}
