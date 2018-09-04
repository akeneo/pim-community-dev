<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\tests\integration\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
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
class GetAssociatedProductCodesByPublishedProductIntegration extends TestCase
{
    public function testQueryToGetAssociatedProductCodes(): void
    {
        $this->createPublishedProduct('productView', ['categories' => ['categoryA']]);
        $this->createPublishedProduct('productNoView', ['categories' => ['categoryC']]);
        $this->createPublishedProduct('productWithoutCategory', []);
        $mainProduct = $this->createPublishedProduct('mainProduct', [
            'categories' => ['categoryA'],
            'associations' => [
                'X_SELL' => ['products' => ['productView']],
                'PACK' => ['products' => ['productNoView', 'productWithoutCategory']],
                'UPSELL' => ['products' => []],
            ]
        ]);

        $associationTypeRepository = $this->get('pim_catalog.repository.association_type');
        $xsell = $associationTypeRepository->findOneByIdentifier('X_SELL');
        $pack = $associationTypeRepository->findOneByIdentifier('PACK');
        $upsell = $associationTypeRepository->findOneByIdentifier('UPSELL');

        $this->generateToken('mary');

        $query = $this->get('pimee_workflow.query.get_associated_product_codes_by_published_product');
        $this->assertSame(['productView'], $query->getCodes($mainProduct->getId(), $mainProduct->getAssociationForType($xsell)));
        $this->assertSame(['productWithoutCategory'], $query->getCodes($mainProduct->getId(), $mainProduct->getAssociationForType($pack)));
        $this->assertSame([], $query->getCodes($mainProduct->getId(), $mainProduct->getAssociationForType($upsell)));
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

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return PublishedProductInterface
     */
    private function createPublishedProduct(string $identifier, array $data): PublishedProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'familyA2');

        $data = array_merge([
            'values'     => [
                'a_metric' => [
                    ['data' => ['amount' => 1, 'unit' => 'WATT'], 'locale' => null, 'scope' => null]
                ],
            ]
        ], $data);

        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $this->get('pimee_workflow.manager.published_product')->publish($product);
    }
}
