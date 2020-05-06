<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepositoryIntegration extends TestCase
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->get('pim_catalog.repository.product');

    }

    /**
     * @test
     */
    public function it_finds_one_by_identifier()
    {
        $productIdentifier = 'a_product';
        $this->createNonVariantProduct($productIdentifier);

        $product = $this->productRepository->findOneByIdentifier($productIdentifier);
    }

    /**
     * @param $productIdentifier
     *
     * @return ProductInterface
     */
    private function createNonVariantProduct($productIdentifier): ProductInterface
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProduct($productIdentifier, 'familyA', [
            'quantified_associations' => [
                'PACK' => [
                    'products' => [
                        ['id' => 1, 'quantity' => 2]
                    ],
                    'product_models' => [
                        ['id' => 1, 'quantity' => 1]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
