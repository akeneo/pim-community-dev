<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Doctrine\Common\Saver;

use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ProductSaverIntegration extends TestCase
{
    public function testProductCreationWithCategoryOnlyViewable()
    {
        $this->generateToken('mary');
        $this->saveProduct(['categories' => ['categoryA1']]);

        $this->assertCount(1, $this->get('pim_catalog.repository.product')->findAll());
    }

    public function testProductCreationWithCategoryOnlyEditable()
    {
        $this->generateToken('mary');
        $this->saveProduct(['categories' => ['categoryA']]);

        $this->assertCount(1, $this->get('pim_catalog.repository.product')->findAll());
    }

    public function testProductCreationWithCategoryOwner()
    {
        $this->generateToken('mary');
        $this->saveProduct(['categories' => ['master']]);

        $this->assertCount(1, $this->get('pim_catalog.repository.product')->findAll());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $username
     */
    private function generateToken($username)
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @param array $data
     */
    private function saveProduct(array $data)
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('sku');
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }
}
