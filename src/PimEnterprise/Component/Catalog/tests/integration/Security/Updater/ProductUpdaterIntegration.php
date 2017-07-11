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
        $this->createProduct(['values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.
     */
    public function testCreateAProductWithAttributeGroupOnlyViewable()
    {
        $this->generateToken('mary');
        $this->createProduct(['values' => ['a_number_float' => [['data' => 12.05, 'locale' => null, 'scope' => null]]]]);
    }

    public function testCreateAProductWithAttributeGroupEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct(['values' => ['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "a_multi_select" does not exist.
     */
    public function testUpdateAProductWithAttributeGroupNotViewable()
    {
        $product = $this->saveProduct(['values' => ['a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_multi_select' => [['data' => ['optionB'], 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage Attribute "a_number_float" belongs to the attribute group "attributeGroupB" on which you only have view permission.
     */
    public function testUpdateAProductWithAttributeGroupOnlyViewableWithChange()
    {
        $product = $this->saveProduct(['values' => ['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_number_float' => [['data' => 14, 'locale' => null, 'scope' => null]]
            ]
        ]);
    }

    public function testUpdateAProductWithAttributeGroupOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct(['values' => ['a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, [
            'values' => [
                'a_number_float' => [['data' => 12, 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->assertSame($product->getValue('a_number_float')->getData(), 12);
    }

    public function testUpdateAProductWithAttributeGroupEditable()
    {
        $product = $this->saveProduct(['values' => ['a_text' => [['data' => 'The text', 'locale' => null, 'scope' => null]]]]);
        $this->generateToken('mary');
        $this->updateProduct($product, [
            'values' => [
                'a_text' => [['data' => 'The text bis', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->assertSame($product->getValue('a_text')->getData(), 'The text bis');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given
     */
    public function testCreateAProductWithLocaleNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage You only have a view permission on the locale "fr_FR"
     */
    public function testCreateAProductWithLocaleOnlyViewable()
    {
        $this->generateToken('mary');
        $this->createProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
    }

    public function testCreateAProductWithLocaleEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);

        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData(), 'text');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Attribute "a_localized_and_scopable_text_area" expects an existing and activated locale, "de_DE" given
     */
    public function testUpdateAProductWithLocaleNotViewable()
    {
        $product = $this->saveProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'de_DE', 'scope' => 'ecommerce']]]]);
    }

    /**
     * @expectedException \PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException
     * @expectedExceptionMessage You only have a view permission on the locale "fr_FR"
     */
    public function testUpdateAProductWithLocaleOnlyViewableWithChange()
    {
        $product = $this->saveProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text FR', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
    }

    public function testUpdateAProductWithLocaleOnlyViewableWithoutChange()
    {
        $product = $this->saveProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]]]);
        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'fr_FR', 'ecommerce')->getData(), 'text');
    }

    public function testUpdateAProductWithLocaleEditable()
    {
        $product = $this->saveProduct(['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);
        $this->generateToken('mary');

        $this->updateProduct($product, ['values' => ['a_localized_and_scopable_text_area' => [['data' => 'text EN', 'locale' => 'en_US', 'scope' => 'ecommerce']]]]);
        $this->assertSame($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData(), 'text EN');
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "categories" expects a valid category code. The category does not exist, "categoryB" given.
     */
    public function testCreateAProductWithCategoryNotViewable()
    {
        $this->generateToken('mary');
        $this->createProduct(['categories' => ['categoryB']]);
    }

    public function testCreateAProductWithCategoryViewable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct(['categories' => ['categoryA2']]);

        $this->assertSame($product->getCategoryCodes(), ['categoryA2']);
    }

    public function testCreateAProductWithCategoryEditable()
    {
        $this->generateToken('mary');
        $product = $this->createProduct(['categories' => ['categoryA']]);

        $this->assertSame($product->getCategoryCodes(), ['categoryA']);
    }

    public function testCreateAProductWithOwnCategory()
    {
        $this->generateToken('mary');
        $product = $this->createProduct(['categories' => ['master']]);

        $this->assertSame($product->getCategoryCodes(), ['master']);
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
     * @param array $data
     *
     * @return ProductInterface
     */
    private function createProduct(array $data)
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('product');
        $this->get('pim_catalog.updater.product')->update($product, $data);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     *
     * @return ProductInterface
     */
    private function updateProduct(ProductInterface $product, array $data)
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);

        return $product;
    }

    /**
     * @param array $data
     *
     * @return ProductInterface
     */
    private function saveProduct(array $data)
    {
        $product = $this->createProduct($data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }
}
