<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductUuidsByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

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
class GetAssociatedProductUuidsByProductIntegration extends TestCase
{
    public function testQueryToGetAssociatedProductCodes()
    {
        $this->createProduct('productView', [new SetCategories(['categoryA'])]);
        $this->createProduct('productNoView', [new SetCategories(['categoryC'])]);
        $this->createProduct('productWithoutCategory', []);
        $mainProduct = $this->createProduct('mainProduct', [
            new SetCategories(['categoryA']),
            new AssociateProducts('X_SELL', ['productView']),
            new AssociateProducts('PACK', ['productNoView', 'productWithoutCategory']),
        ]);

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('mary');

        $productAssociations = [];
        foreach ($mainProduct->getAssociations() as $productAssociation) {
            $productAssociations[$productAssociation->getAssociationType()->getCode()] = $productAssociation;
        }

        $query = $this->getQuery();
        $this->assertSame(['productView'], $query->getIdentifiers($mainProduct->getUuid(), $productAssociations['X_SELL']));
        $this->assertSame(['productWithoutCategory'], $query->getIdentifiers($mainProduct->getUuid(), $productAssociations['PACK']));
        $this->assertSame([], $query->getIdentifiers($mainProduct->getUuid(), $productAssociations['UPSELL']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    private function getQuery(): GetAssociatedProductUuidsByProduct
    {
        return $this->get('pim_catalog.query.get_associated_product_uuids_by_product');
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');

        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier($identifier), $userIntents)
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);
        Assert::assertNotFalse($id);

        return \intval($id);
    }
}
