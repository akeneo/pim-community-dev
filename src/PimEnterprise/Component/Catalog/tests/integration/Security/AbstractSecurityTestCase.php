<?php

namespace PimEnterprise\Component\Catalog\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AbstractSecurityTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $username
     */
    protected function generateToken($username): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
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
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct(string $identifier): ProductInterface
    {
        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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
     * @param string $identifier
     *
     * @return array
     */
    protected function getCategoriesFromDatabase(string $identifier): array
    {
        $sql = <<<SQL
SELECT c.code
FROM pim_catalog_product p
INNER JOIN pim_catalog_category_product cp ON p.id = cp.product_id
INNER JOIN pim_catalog_category c ON c.id = cp.category_id
WHERE identifier = :identifier
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('identifier', $identifier);
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
        $sql = <<<SQL
SELECT t.code, associated_product.identifier
FROM pim_catalog_product p 
INNER JOIN pim_catalog_association a ON a.owner_id = p.id
INNER JOIN pim_catalog_association_type t ON t.id = a.association_type_id
INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
INNER JOIN pim_catalog_product associated_product ON associated_product.id = ap.product_id 
WHERE p.identifier = :identifier
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('identifier', $identifier);
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
        $sql = <<<SQL
SELECT p.raw_values FROM pim_catalog_product p WHERE p.identifier = :identifier
SQL;

        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('identifier', $identifier);
        $stmt->execute();

        return $stmt->fetch();
    }
}
