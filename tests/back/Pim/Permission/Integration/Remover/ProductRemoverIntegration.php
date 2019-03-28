<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Remover;

use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * +----------+-----------------------------------------------+
 * |          |                   Categories                  |
 * +  Roles   +-----------------------------------------------+
 * |          |   categoryA   |   categoryA1  |   categoryB   |
 * +----------+-----------------------------------------------+
 * | Redactor |   View,Edit   |     View      |               |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit,Own |
 * +----------+-----------------------------------------------+
 */
class ProductRemoverIntegration extends TestCase
{
    public function testRemoveOneProductSuccessful()
    {
        $remover = $this->get('pim_catalog.remover.product');
        $product = $this->createProduct('deleted_product', [
            'categories' => ['categoryB']
        ]);

        $this->generateToken('julia');
        $remover->remove($product);

        $expectedDeletedProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier($product->getIdentifier());
        $this->assertNull($expectedDeletedProduct);
    }

    public function testRemoveOneProductWithoutCategory()
    {
        $remover = $this->get('pim_catalog.remover.product');
        $product = $this->createProduct('product_without_category', []);

        $this->generateToken('julia');
        $remover->remove($product);

        $expectedDeletedProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier($product->getIdentifier());
        $this->assertNull($expectedDeletedProduct);
    }

    public function testRemoveAllProductsSuccessful()
    {
        $remover = $this->get('pim_catalog.remover.product');
        $productRepository = $this->get('pim_catalog.repository.product');

        $firstProduct = $this->createProduct('first_product', [
            'categories' => ['categoryA']
        ]);
        $secondProduct = $this->createProduct('second_product', [
            'categories' => ['categoryB']
        ]);

        $this->generateToken('julia');
        $remover->removeAll([$firstProduct, $secondProduct]);

        $expectedDeletedFirstProduct = $productRepository->findOneByIdentifier($firstProduct->getIdentifier());
        $this->assertNull($expectedDeletedFirstProduct);

        $expectedDeletedSecondProduct = $productRepository->findOneByIdentifier($secondProduct->getIdentifier());
        $this->assertNull($expectedDeletedSecondProduct);
    }

    public function testRemoveAnUnclassifiedProduct()
    {
        $remover = $this->get('pim_catalog.remover.product');
        $productRepository = $this->get('pim_catalog.repository.product');
        $unclassifiedProduct = $this->createProduct('unclassified_product');

        $this->generateToken('mary');
        $remover->remove($unclassifiedProduct);

        $expectedDeletedProduct = $productRepository->findOneByIdentifier($unclassifiedProduct->getIdentifier());
        $this->assertNull($expectedDeletedProduct);
    }

    public function testProductIsNotDeletableByUserWhoCanEditBuIsNotOwner()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You can delete a product only if it is classified in at least one category on which you have an own permission.');

        $remover = $this->get('pim_catalog.remover.product');
        $product = $this->createProduct('product_to_delete', [
            'categories' => ['categoryA']
        ]);

        $this->generateToken('mary');

        $remover->remove($product);
    }

    public function testOneOfTheProductsIsNotDeletableByUserWhoCanEditBuIsNotOwner()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You can delete a product only if it is classified in at least one category on which you have an own permission.');

        $remover = $this->get('pim_catalog.remover.product');

        $firstProduct = $this->createProduct('first_product', [
            'categories' => ['categoryA']
        ]);
        $secondProduct = $this->createProduct('second_product', [
            'categories' => ['categoryA']
        ]);

        $this->generateToken('mary');
        $remover->removeAll([$firstProduct, $secondProduct]);
    }

    public function testProductIsNotDeletableByUserWhoCanOnlyViewProduct()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You can delete a product only if it is classified in at least one category on which you have an own permission.');

        $remover = $this->get('pim_catalog.remover.product');
        $product = $this->createProduct('product_to_delete', [
            'categories' => ['categoryA1']
        ]);

        $this->generateToken('mary');

        $remover->remove($product);
    }

    public function testOneOfTheProductsIsNotDeletableByUserWhoCanOnlyViewProduct()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You can delete a product only if it is classified in at least one category on which you have an own permission.');

        $remover = $this->get('pim_catalog.remover.product');

        $firstProduct = $this->createProduct('first_product', [
            'categories' => ['categoryA1']
        ]);
        $secondProduct = $this->createProduct('second_product', [
            'categories' => ['categoryA1']
        ]);

        $this->generateToken('mary');
        $remover->removeAll([$firstProduct, $secondProduct]);
    }

    public function testProductNotDeletableByUserWhoHasNoPermissions()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You can delete a product only if it is classified in at least one category on which you have an own permission.');

        $remover = $this->get('pim_catalog.remover.product');
        $product = $this->createProduct('product_to_delete', [
            'categories' => ['categoryB']
        ]);

        $this->generateToken('mary');

        $remover->remove($product);
    }

    public function testOneOfTheProductsIsNotDeletableByUserWhoHasNoPermissions()
    {
        $this->expectException(ResourceAccessDeniedException::class);
        $this->expectExceptionMessage('You can delete a product only if it is classified in at least one category on which you have an own permission.');

        $remover = $this->get('pim_catalog.remover.product');

        $firstProduct = $this->createProduct('first_product', [
            'categories' => ['categoryB']
        ]);
        $secondProduct = $this->createProduct('second_product', [
            'categories' => ['categoryB']
        ]);

        $this->generateToken('mary');
        $remover->removeAll([$firstProduct, $secondProduct]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param string $username
     */
    private function generateToken(string $username): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }
}
