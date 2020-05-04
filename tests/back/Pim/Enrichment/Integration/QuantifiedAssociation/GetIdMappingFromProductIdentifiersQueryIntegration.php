<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdentifiersQueryInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductIdentifiersQueryIntegration extends TestCase
{
    /** @var GetIdMappingFromProductIdentifiersQueryInterface */
    private $getIdMappingFromProductIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getIdMappingFromProductIdentifiersQuery = $this->get('akeneo.pim.enrichment.product.query.quantified_association.get_id_mapping_from_product_identifiers_query');
    }

    /**
     * @test
     */
    public function it_fetches_the_id_mappings_given_some_product_identifiers()
    {
        $productIdentifier = 'product_1';
        $expectedId = 1;
        $this->createProduct($productIdentifier);

        $idMapping = $this->getIdMappingFromProductIdentifiersQuery->execute([$productIdentifier]);

        $actualId = $idMapping->getId($productIdentifier);
        self::assertEquals($expectedId, $actualId);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $productIdentifier): void
    {
        $product = new Product();
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'family' => 'familyA',
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => $productIdentifier,
                        ],
                    ],
                ],
            ]
        );

        $this->get('pim_catalog.saver.product')->save($product);
    }
}
