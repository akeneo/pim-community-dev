<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Doctrine\Common\Collections\ArrayCollection;

class CreateTwoWayAssociationIntegration extends TestCase
{
    public function testInvertedAssociationAreAutomaticallyCreatedOnTwoWayAssociation()
    {
        $aProduct = $this->getEntityBuilder()->createProduct('a_product', '', []);
        $aProductModel = $this->getEntityBuilder()->createProductModel('a_product_model', 'clothing_color_size', null, []);
        $anotherProduct = $this->getEntityBuilder()->createProduct('another_product', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["a_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $productWithAssociation = $this->getEntityBuilder()->createProduct('a_product_with_associations', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["another_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $this->getProductAssociationRepository()->clear();
        $this->getProductModelAssociationRepository()->clear();
        $productAssociations = $this->getProductAssociationRepository()->findByOwner($aProduct->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productAssociations, ['another_product'], []);

        $anotherProductAssociation = $this->getProductAssociationRepository()->findByOwner($anotherProduct->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($anotherProductAssociation, ['a_product', 'a_product_with_associations'], ['a_product_model']);

        $productWithAssociationAssociation = $this->getProductAssociationRepository()->findByOwner($productWithAssociation->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productWithAssociationAssociation, ['another_product'], ['a_product_model']);

        $productModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productModelAssociations, ['another_product', 'a_product_with_associations'], []);
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
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productAssociations, [], []);
    }

    public function testInvertedAssociationsWasDeletedWhenProductAssociationIsDeleted()
    {
        $aProduct = $this->getEntityBuilder()->createProduct('a_product', '', []);
        $aProductModel = $this->getEntityBuilder()->createProductModel('a_product_model', 'clothing_color_size', null, []);
        $anotherProduct = $this->getEntityBuilder()->createProduct('another_product', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["a_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $productWithAssociation = $this->getEntityBuilder()->createProduct('a_product_with_associations', '', [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => ["another_product"],
                    "product_models" => ["a_product_model"],
                ],
            ],
        ]);

        $this->getProductUpdater()->update($productWithAssociation, [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => [],
                    "product_models" => [],
                ],
            ],
        ]);

        $this->getProductSaver()->save($productWithAssociation);

        $aProductAssociation = $this->getProductAssociationRepository()->findByOwner($aProduct->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductAssociation, ['another_product'], []);

        $aProductAssociation = $this->getProductAssociationRepository()->findByOwner($anotherProduct->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductAssociation, ['a_product'], ['a_product_model']);

        $productModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productModelAssociations, ['another_product'], []);

        $productWithAssociationAssociations = $this->getProductAssociationRepository()->findByOwner($productWithAssociation);
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productWithAssociationAssociations, [], []);
    }

    private function assertContainsCompatibilityAssociationWithProductsAndProductModels(array $associations, $products, $productModels)
    {
        $compatibilityAssociation = $this->extractCompatibilityAssociation($associations);

        $this->assertCount(count($products), $compatibilityAssociation->getProducts());
        foreach ($compatibilityAssociation->getProducts() as $associationProduct) {
            $this->assertContains($associationProduct->getIdentifier(), $products);
        }

        $this->assertCount(count($productModels), $compatibilityAssociation->getProductModels());
        foreach ($compatibilityAssociation->getProductModels() as $associationProductModel) {
            $this->assertContains($associationProductModel->getCode(), $productModels);
        }
    }

    private function extractCompatibilityAssociation(array $associations): AssociationInterface
    {
        foreach ($associations as $association) {
            if ($association->getAssociationType()->getCode() === "COMPATIBILITY") {
                return $association;
            }
        }

        $this->fail('No compatibility association was found');
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

    private function getProductUpdater(): ProductUpdater
    {
        return $this->get('pim_catalog.updater.product');
    }

    private function getProductSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.product');
    }
}
