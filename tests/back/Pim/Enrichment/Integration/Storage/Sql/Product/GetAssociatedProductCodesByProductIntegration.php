<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Test\Integration\TestCase;

class GetAssociatedProductCodesByProductIntegration extends TestCase
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

        $associationTypeRepository = $this->get('pim_catalog.repository.association_type');
        $xsell = $associationTypeRepository->findOneByIdentifier('X_SELL');
        $pack = $associationTypeRepository->findOneByIdentifier('PACK');
        $upsell = $associationTypeRepository->findOneByIdentifier('UPSELL');

        $query = $this->get('pim_catalog.query.get_associated_product_codes_by_product');
        $this->assertSame(['productB'], $query->getCodes($productA->getId(), $productA->getAssociationForType($xsell)));
        $this->assertSame(['productC', 'productD'], $query->getCodes($productA->getId(), $productA->getAssociationForType($pack)));
        $this->assertSame([], $query->getCodes($productA->getId(), $productA->getAssociationForType($upsell)));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
