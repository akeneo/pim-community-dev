<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductModelCodesQueryIntegration extends TestCase
{
    /** @var GetIdMappingFromProductModelCodesQueryInterface */
    private $getIdMappingFromProductModelCodesQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getIdMappingFromProductModelCodesQuery = $this->get('akeneo.pim.enrichment.product.query.quantified_association.get_id_mapping_from_product_model_codes_query');
    }

    /**
     * @test
     */
    public function it_fetches_the_id_mappings_given_some_product_model_codes()
    {
        $productModelCode = 'product_model_1';
        $expectedId = 1;
        $this->createProductModel($productModelCode);

        $idMapping = $this->getIdMappingFromProductModelCodesQuery->execute([$productModelCode]);

        $actualId = $idMapping->getId($productModelCode);
        self::assertEquals($expectedId, $actualId);
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
