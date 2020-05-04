<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductModelIdsQueryIntegration extends TestCase
{
    /** @var GetIdMappingFromProductModelIdsQueryInterface */
    private $getIdMappingFromProductModelIdsQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getIdMappingFromProductModelIdsQuery = $this->get('akeneo.pim.enrichment.product.query.quantified_association.get_id_mapping_from_product_model_ids_query');
    }

    /**
     * @test
     */
    public function it_fetches_the_id_mappings_given_some_product_model_ids()
    {
        $expectedProductModelCode = 'product_1';
        $productModelId = 1;
        $this->createProductModel($expectedProductModelCode);

        $idMapping = $this->getIdMappingFromProductModelIdsQuery->execute([$productModelId]);

        $actualProductModelCode = $idMapping->getIdentifier($productModelId);
        self::assertEquals($expectedProductModelCode, $actualProductModelCode);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(string $productModelCode): void
    {
        $productModel = new ProductModel();
        $this->get('pim_catalog.updater.product_model')
            ->update(
                $productModel,
                [
                    'code'           => $productModelCode,
                    'parent'         => null,
                    'family_variant' => 'familyVariantA1',
                ]
            );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }
}
