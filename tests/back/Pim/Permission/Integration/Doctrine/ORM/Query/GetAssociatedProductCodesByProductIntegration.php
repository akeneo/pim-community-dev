<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Doctrine\ORM\Query;

use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * +----------+-------------------------------+
 * |          |            Categories         |
 * +  Roles   +-------------------------------+
 * |          |   categoryA   |   categoryC   |
 * +----------+-------------------------------+
 * | Redactor |   View,Edit   |               |
 * | Manager  | View,Edit,Own | View,Edit,Own |
 * +----------+-------------------------------+
 */
class GetAssociatedProductCodesByProductIntegration extends TestCase
{
    public function testQueryToGetAssociatedProductCodes()
    {
        $mainProduct = $this->get('pim_catalog.builder.product')->createProduct('mainProduct');
        $productView = $this->get('pim_catalog.builder.product')->createProduct('productView');
        $productNoView = $this->get('pim_catalog.builder.product')->createProduct('productNoView');
        $productWithoutCategory = $this->get('pim_catalog.builder.product')->createProduct('productWithoutCategory');

        $this->get('pim_catalog.saver.product')->saveAll([$productView, $productNoView, $productWithoutCategory]);

        $this->get('pim_catalog.updater.product')->update($mainProduct, [
            'categories' => ['categoryA'],
            'associations' => [
                'X_SELL' => ['products' => ['productView']],
                'PACK' => ['products' => ['productNoView', 'productWithoutCategory']],
                'UPSELL' => ['products' => []],
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($mainProduct);

        $this->get('pim_catalog.updater.product')->update($productView, ['categories' => ['categoryA']]);
        $this->get('pim_catalog.saver.product')->save($productView);
        $this->get('pim_catalog.updater.product')->update($productNoView, ['categories' => ['categoryC']]);
        $this->get('pim_catalog.saver.product')->save($productNoView);

        $this->generateToken('mary');

        $productAssociations = [];
        foreach ($mainProduct->getAssociations() as $productAssociation) {
            $productAssociations[$productAssociation->getAssociationType()->getCode()] = $productAssociation;
        }

        $query = $this->get('pim_catalog.query.get_associated_product_codes_by_product');
        $this->assertSame(['productView'], $query->getCodes($mainProduct->getId(), $productAssociations['X_SELL']));
        $this->assertSame(['productWithoutCategory'], $query->getCodes($mainProduct->getId(), $productAssociations['PACK']));
        $this->assertSame([], $query->getCodes($mainProduct->getId(), $productAssociations['UPSELL']));
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
    private function generateToken(string $username): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }
}
