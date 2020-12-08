<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Doctrine\Common\Collections\ArrayCollection;

class CreateTwoWayAssociationProductModelIntegration extends TestCase
{
    public function testInvertedAssociationAreAutomaticallyCreatedOnTwoWayAssociation()
    {
        $aProduct = $this->createAndSaveProduct('a_product');
        $aProductModel = $this->createAndSaveProductModel('a_product_model', 'clothing_color_size', [], []);
        $anotherProductModel = $this->createAndSaveProductModel('another_product_model', 'clothing_color_size', ['a_product'], ['a_product_model']);
        $aProductModelWithAssociation = $this->createAndSaveProductModel('a_product_model_with_association', 'clothing_color_size', [], ['a_product_model', 'another_product_model']);

        $this->getProductModelAssociationRepository()->clear();

        $productAssociations = $this->getProductAssociationRepository()->findByOwner($aProduct->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productAssociations, [], ['another_product_model']);

        $aProductModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductModelAssociations, [], ['another_product_model', 'a_product_model_with_association']);

        $anotherProductModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($anotherProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($anotherProductModelAssociations, ['a_product'], ['a_product_model', 'a_product_model_with_association']);

        $aProductModelWithAssociationAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModelWithAssociation->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductModelWithAssociationAssociations, [], ['a_product_model', 'another_product_model']);
    }

    public function testAssociationsWasDeletedWhenAssociatedProductIsDeleted()
    {
        $aProduct = $this->createAndSaveProduct('a_product');
        $aProductModel = $this->createAndSaveProductModel('a_product_model', 'clothing_color_size', [], []);
        $anotherProductModel = $this->createAndSaveProductModel('another_product_model', 'clothing_color_size', ['a_product'], ['a_product_model']);
        $aProductModelWithAssociation = $this->createAndSaveProductModel('a_product_model_with_association', 'clothing_color_size', [], ['a_product_model', 'another_product_model']);

        $this->getProductRemover()->remove($aProduct);
        $this->getProductModelRemover()->remove($aProductModel);
        $this->getProductModelAssociationRepository()->clear();

        $anotherProductModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($anotherProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($anotherProductModelAssociations, [], ['a_product_model_with_association']);

        $aProductModelWithAssociationAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModelWithAssociation->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductModelWithAssociationAssociations, [], ['another_product_model']);
    }
/* TODO
    public function testInvertedAssociationsWasDeletedWhenProductAssociationIsDeleted()
    {
        $aProduct = $this->createAndSaveProduct('a_product');
        $aProductModel = $this->createAndSaveProductModel('a_product_model', 'clothing_color_size', [], []);
        $anotherProductModel = $this->createAndSaveProductModel('another_product_model', 'clothing_color_size', ['a_product'], ['a_product_model']);
        $aProductModelWithAssociation = $this->createAndSaveProductModel('a_product_model_with_association', 'clothing_color_size', [], ['a_product_model', 'another_product_model']);

        $this->getProductModelUpdater()->update($anotherProductModel, [
            'associations'  => [
                "COMPATIBILITY" => [
                    "products" => [],
                    "product_models" => [],
                ],
            ],
        ]);
        $this->getProductModelSaver()->save($anotherProductModel);

        $this->getProductModelAssociationRepository()->clear();

        $productAssociations = $this->getProductAssociationRepository()->findByOwner($aProduct->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($productAssociations, [], []);

        $aProductModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductModelAssociations, [], ['a_product_model_with_association']);

        $anotherProductModelAssociations = $this->getProductModelAssociationRepository()->findByOwner($anotherProductModel->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($anotherProductModelAssociations, [], []);

        $aProductModelWithAssociationAssociations = $this->getProductModelAssociationRepository()->findByOwner($aProductModelWithAssociation->getId());
        $this->assertContainsCompatibilityAssociationWithProductsAndProductModels($aProductModelWithAssociationAssociations, [], ['a_product_model']);
    }
*/
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

    private function createAndSaveProduct(string $identifier): ProductInterface
    {
        return $this->getEntityBuilder()->createProduct($identifier, '', []);
    }


    private function createAndSaveProductModel(
        string $identifier,
        string $familyVariantCode,
        array $productIdentifiersAssociated,
        array $productModelCodeAssociated
    ): ProductModelInterface {
        $data = [];
        if (!empty($productIdentifiersAssociated)) {
            $data['associations']['COMPATIBILITY']['products'] = $productIdentifiersAssociated;
        }

        if (!empty($productModelCodeAssociated)) {
            $data['associations']['COMPATIBILITY']['product_models'] = $productModelCodeAssociated;
        }

        return $this->getEntityBuilder()->createProductModel(
            $identifier,
            $familyVariantCode,
            null,
            $data
        );
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

    private function getProductModelAssociationRepository()
    {
        return $this->get('doctrine')->getManager()->getRepository(ProductModelAssociation::class);
    }

    private function getProductRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    private function getProductModelUpdater(): ObjectUpdaterInterface
    {
        return $this->get('pim_catalog.updater.product_model');
    }

    private function getProductModelRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product_model');
    }

    private function getProductModelSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.product_model');
    }
}
