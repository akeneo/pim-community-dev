<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Security;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AbstractSecurityTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    /**
     * @param string $username
     */
    protected function generateToken($username): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        return $product;
    }

    /**
     * @param string $code
     * @param array  $data
     *
     * @return ProductModelInterface
     */
    protected function createProductModel(string $code, array $data): ProductModelInterface
    {
        $productModel = new ProductModel();
        $productModel->setCode($code);
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     *
     * @return ProductInterface
     */
    protected function updateProduct(ProductInterface $product, array $data): ProductInterface
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);

        return $product;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param array $data
     *
     * @return ProductModelInterface
     */
    protected function updateProductModel(ProductModelInterface $productModel, array $data): ProductModelInterface
    {
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        return $productModel;
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct(string $identifier): ProductInterface
    {
        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    /**
     * @param string $code
     *
     * @return ProductModelInterface
     */
    protected function getProductModel(string $code): ProductModelInterface
    {
        return $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function saveProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->createProduct($identifier, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param string $code
     * @param array  $data
     *
     * @return ProductModelInterface
     */
    protected function saveProductModel(string $code, array $data): ProductModelInterface
    {
        $productModel = $this->createProductModel($code, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    protected function getCategoriesFromDatabase(string $identifier): array
    {
        $productUuid = $this->getProductUuid($identifier);

        $sql = <<<SQL
SELECT c.code
FROM pim_catalog_product p
INNER JOIN pim_catalog_category_product cp ON p.uuid = cp.product_uuid
INNER JOIN pim_catalog_category c ON c.id = cp.category_id
WHERE uuid = :uuid
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('uuid', $productUuid->getBytes());
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param string $code
     *
     * @return array
     */
    protected function getCategoriesFromDatabaseForProductModel(string $code): array
    {
        $sql = <<<SQL
SELECT c.code
FROM pim_catalog_product_model pm
INNER JOIN pim_catalog_category_product_model cpm ON pm.id = cpm.product_model_id
INNER JOIN pim_catalog_category c ON c.id = cpm.category_id
WHERE pm.code = :code
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('code', $code);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    protected function getAssociationFromDatabase(string $identifier): array
    {
        $uuid = $this->getProductUuid($identifier);

        $sql = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT t.code, pcpud.raw_data AS identifier
FROM pim_catalog_product p
    INNER JOIN pim_catalog_association a ON a.owner_uuid = p.uuid
    INNER JOIN pim_catalog_association_type t ON t.id = a.association_type_id
    INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product associated_product ON associated_product.uuid = ap.product_uuid
    LEFT JOIN pim_catalog_product_unique_data pcpud
        ON pcpud.product_uuid = associated_product.uuid
        AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE p.uuid = :uuid
ORDER BY t.code ASC, pcpud.raw_data ASC
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('uuid', $uuid->getBytes());
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    protected function getValuesFromDatabase(string $identifier): array
    {
        $uuid = $this->getProductUuid($identifier);

        $sql = <<<SQL
SELECT p.raw_values FROM pim_catalog_product p WHERE p.uuid = :uuid
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('uuid', $uuid->getBytes());
        $stmt->execute();

        return $stmt->fetch();
    }

    protected function createOrUpdateProduct(string $identifier, array $userIntents = [], string $userName = 'admin'): ProductInterface
    {
        $userId = $this->loginAs($userName);

        $command = UpsertProductCommand::createWithIdentifier(
            userId: $userId,
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function loginAs(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertInstanceOf(UserInterface::class, $user);
        $this->get('security.token_storage')->setToken(
            new UsernamePasswordToken($user, 'main', $user->getRoles())
        );

        return (int) $user->getId();
    }
}
