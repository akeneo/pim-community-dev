<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Test\Integration\TestCase;

class GetAssociatedProductUuidsByProductIntegration extends TestCase
{
    public function testQueryToGetAssociatedProductCodes()
    {
        $productA = $this->get('pim_catalog.builder.product')->createProduct('productA');
        $productB = $this->get('pim_catalog.builder.product')->createProduct('productB');
        $productC = $this->get('pim_catalog.builder.product')->createProduct('productC');
        $productD = $this->get('pim_catalog.builder.product')->createProduct('productD');

        $this->get('pim_catalog.saver.product')->saveAll([$productB, $productC, $productD]);

        $this->get('pim_catalog.updater.product')->update($productA, [
            'associations' => [
                'X_SELL' => ['products' => ['productB']],
                'PACK' => ['products' => ['productC', 'productD']],
                'UPSELL' => ['products' => []],
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($productA);

        $productAssociations = [];
        foreach ($productA->getAssociations() as $productAssociation) {
            $productAssociations[$productAssociation->getAssociationType()->getCode()] = $productAssociation;
        }

        $query = $this->get('pim_catalog.query.get_associated_product_uuids_by_product');
        $this->assertEqualsCanonicalizing([$productB->getUuid()->toString()], $query->getUuids($productA->getUuid(), $productAssociations['X_SELL']));
        $this->assertEqualsCanonicalizing([$productC->getUuid()->toString(), $productD->getUuid()->toString()], $query->getUuids($productA->getUuid(), $productAssociations['PACK']));
        $this->assertEqualsCanonicalizing([], $query->getUuids($productA->getUuid(), $productAssociations['UPSELL']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
