<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;

class CreateTwoWayAssociationIntegration extends TestCase
{
    public function testInvertedAssociationAreAutomaticallyCreatedOnTwoWayAssociation()
    {
        $product = $this->getEntityBuilder()->createProduct('a_product', '', []);
        $productModel = $this->getEntityBuilder()->createProductModel('a_product_model', 'clothing_color_size', null, []);

        $productWithAssociations = $this->getEntityBuilder()->createProduct('a_product_with_associations', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["a_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $productAssociations = $this->getProductAssociationRepository()->findByOwner($product->getId());
        $this->assertProductIsInCompatibilityAssociation($productAssociations, $productWithAssociations);

        $productModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($productModel->getId());
        $this->assertProductIsInCompatibilityAssociation($productModelAssociations, $productWithAssociations);
    }

    public function testAssociationsWasDeletedWhenAssociatedProductIsDeleted()
    {
        $product = $this->getEntityBuilder()->createProduct('a_product', '', []);
        $productModel = $this->getEntityBuilder()->createProductModel('a_product_model', 'clothing_color_size', null, []);
        $productWithAssociations = $this->getEntityBuilder()->createProduct('a_product_with_associations', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["a_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $product = $this->getProductRepository()->findOneByIdentifier($product->getIdentifier());
        $this->getProductRemover()->remove($product);
        $this->getProductModelRemover()->remove($productModel);

        $this->getProductAssociationRepository()->clear();
        $productAssociations = $this->getProductAssociationRepository()->findByOwner($productWithAssociations->getId());
        $this->assertAssociationsHaveNoProduct($productAssociations);

        $this->getProductModelAssociationRepository()->clear();
        $productModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($productWithAssociations->getId());
        $this->assertAssociationsHaveNoProductModel($productModelAssociations);
    }

    public function testInvertedAssociationsWasDeletedWhenProductAssociationIsDeleted()
    {
        $this->getEntityBuilder()->createProduct('a_product', '', []);
        $this->getEntityBuilder()->createProductModel('a_product_model', 'clothing_color_size', null, []);
        $product = $this->getEntityBuilder()->createProduct('a_product_with_associations', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["a_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $product->getAssociationForTypeCode('COMPATIBILITY')->setProducts([]);
        $product->getAssociationForTypeCode('COMPATIBILITY')->setProductModels([]);
        $this->getProductSaver()->save($product);

        $productAssociations = $this->getProductAssociationRepository()->findByOwner($product->getId());
        $this->assertAssociationsHaveNoProduct($productAssociations);

        $productModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($product->getId());
        $this->assertAssociationsHaveNoProductModel($productModelAssociations);
    }

    private function assertAssociationsHaveNoProductModel(array $associations)
    {
        foreach ($associations as $association) {
            $this->assertEmpty($association->getProductModels());
        }
    }

    private function assertAssociationsHaveNoProduct(array $associations)
    {
        foreach ($associations as $association) {
            $this->assertEmpty($association->getProducts());
        }
    }

    private function assertProductIsInCompatibilityAssociation(array $associations, ProductInterface $product)
    {
        $compatibilityAssociations = array_filter($associations, function (AssociationInterface $association) {
            return $association->getAssociationType()->getCode() === "COMPATIBILITY";
        });

        $this->assertArrayHasKey(0, $compatibilityAssociations);
        $compatibilityAssociation = current($compatibilityAssociations);
        $this->assertTrue($compatibilityAssociation->hasProduct($product));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function getEntityBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
    }

    private function getProductAssociationRepository(): AssociationRepositoryInterface
    {
        return $this->get('pim_catalog.repository.association');
    }

    private function getProductRepository()
    {
        return $this->get('pim_api.repository.product');
    }
    private function getProductModelAssociationRepository()
    {
        return $this->get('doctrine')->getManager()->getRepository(ProductModelAssociation::class);
    }

    private function getProductRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    private function getProductModelRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product_model');
    }

    private function getProductSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.product');
    }
}
