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
    public function it_return_nothing_when_nothing_passed()
    {
        self::assertEquals([], $this->findNonExistingProductModelCodesQuery->execute([]));
    }

    /**
     * @test
     */
    public function it_returns_the_product_model_codes_that_does_not_exists()
    {
        $existingProductModelCodes = [
            'product_model_1',
            'product_model_2',
            'product_model_3',
            'product_model_4',
            'product_model_5',
        ];

        foreach ($existingProductModelCodes as $productModelCode) {
            $this->createProductModel($productModelCode);
        }

        $lookupProductModelCodes = [
            'product_model_1',
            'product_model_2',
            'product_model_3',
            'product_model_does_not_exists',
        ];

        $actualNonExistingProductModelCodes = $this->findNonExistingProductModelCodesQuery->execute(
            $lookupProductModelCodes
        );
        $expectedNonExistingProductModelCodes = [
            'product_model_does_not_exists',
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
