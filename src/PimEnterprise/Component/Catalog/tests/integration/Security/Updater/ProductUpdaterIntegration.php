<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security\Updater;

use Akeneo\Test\Integration\Configuration;
use Akeneo\TestEnterprise\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ProductUpdaterIntegration extends TestCase
{
    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "a_multi_select" does not exist.
     */
    public function testCreateAProductWithAttributeGroupNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct(['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.
     */
    public function testCreateAProductWithAttributeGroupOnlyViewable()
    {
        $this->generateToken('mary');
        $this->createProduct(['a_number_float' => [['data' => 12.05, 'locale' => null, 'scope' => null]]]);
    }

    public function testCreateAProductWithAttributeGroupEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct(['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "a_multi_select" does not exist.
     */
    public function testUpdateAProductWithAttributeGroupNotViewable()
    {
        $product = $this->saveProduct(['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]
        ]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.
     */
    public function testUpdateAProductWithAttributeGroupOnlyViewableWithChange()
    {
        $product = $this->saveProduct(['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'a_number_float' => [['data' => 14, 'locale' => null, 'scope' => null]]
        ]);
    }

    public function testUpdateAProductWithAttributeGroupOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct(['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]
        ]);

        $this->assertSame($product->getValue('a_number_float')->getData(), 12);
    }

    public function testUpdateAProductWithAttributeGroupEditable()
    {
        $product = $this->saveProduct(['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]);
        $this->generateToken('mary');
        $this->updateProduct($product, [
            'a_text' => [['data' => 'The text bis', 'locale' => null, 'scope' => null]]
        ]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text bis');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
    }

    /**
     * @param string $username
     */
    private function generateToken($username)
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @param array $values
     *
     * @return ProductInterface
     */
    private function createProduct(array $values)
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('product');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $values
     *
     * @return ProductInterface
     */
    private function updateProduct(ProductInterface $product, array $values)
    {
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);

        return $product;
    }

    /**
     * @param array $values
     *
     * @return ProductInterface
     */
    private function saveProduct(array $values)
    {
        $product = $this->createProduct($values);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
