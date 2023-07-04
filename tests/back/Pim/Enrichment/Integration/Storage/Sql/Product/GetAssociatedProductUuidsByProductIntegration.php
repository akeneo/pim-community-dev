<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;

class GetAssociatedProductUuidsByProductIntegration extends AbstractProductTestCase
{
    const PRODUCT_A = '8996683d-e7e5-4eb9-8e21-bd05ff35079b';
    const PRODUCT_B = '17ccf9b2-a592-4e87-8e40-687504ee4d93';
    const PRODUCT_C = '02b77c8d-c69b-48c8-bf6e-99b8dcd28262';
    const PRODUCT_D = '58c184fb-a56a-4d97-9145-fc6e4f9602b3';

    public function testQueryToGetAssociatedProductCodes()
    {
        $this->createProductWithUuid(self::PRODUCT_B, [
            new SetIdentifierValue('sku', 'productB'),
        ]);
        $this->createProductWithUuid(self::PRODUCT_C, [
            new SetIdentifierValue('sku', 'productC'),
        ]);
        $this->createProductWithUuid(self::PRODUCT_D, [
            new SetIdentifierValue('sku', 'productD'),
        ]);

        $productA = $this->createProductWithUuid(self::PRODUCT_A, [
            new AssociateProducts('X_SELL', ['productB']),
            new AssociateProducts('PACK', ['productC', 'productD']),
        ]);

        $productAssociations = [];
        foreach ($productA->getAssociations() as $productAssociation) {
            $productAssociations[$productAssociation->getAssociationType()->getCode()] = $productAssociation;
        }

        $query = $this->get('pim_catalog.query.get_associated_product_uuids_by_product');
        $this->assertEqualsCanonicalizing([self::PRODUCT_B], $query->getUuids(Uuid::fromString(self::PRODUCT_A), $productAssociations['X_SELL']));
        $this->assertEqualsCanonicalizing([self::PRODUCT_C, self::PRODUCT_D], $query->getUuids(Uuid::fromString(self::PRODUCT_A), $productAssociations['PACK']));
        $this->assertEqualsCanonicalizing([], $query->getUuids(Uuid::fromString(self::PRODUCT_A), $productAssociations['UPSELL']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
