<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Query\AssociatedProduct\GetAssociatedProductCodesByProduct;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetAssociatedProductCodesByProductFromDB implements GetAssociatedProductCodesByProduct
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface  $tokenStorage
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->connection = $entityManager->getConnection();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodes($productId, $associationTypeId)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $userGroupsIds = $user->getGroupsIds();

        $sql = <<<SQL
SELECT DISTINCT(p.identifier) as code
FROM pim_catalog_association a
    INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product p ON p.id = ap.product_id AND p.product_type IN ('product', 'variant_product')
    LEFT JOIN pim_catalog_category_product cp on p.id = cp.product_id
    LEFT JOIN pimee_security_product_category_access pca ON pca.category_id = cp.category_id AND pca.user_group_id IN (:userGroupsIds)
WHERE a.owner_id = :ownerId AND a.association_type_id = :associationTypeId
    AND (cp.category_id IS NULL OR pca.view_items = 1)
ORDER BY p.identifier ASC;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('ownerId', $productId, Type::INTEGER);
        $stmt->bindValue('associationTypeId', $associationTypeId, Type::INTEGER);
        $stmt->bindValue('userGroupsIds', $userGroupsIds, Type::SIMPLE_ARRAY);
        $stmt->execute();

        $codes = array_map(function ($row) {
            return $row['code'];
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        return $codes;
    }
}
