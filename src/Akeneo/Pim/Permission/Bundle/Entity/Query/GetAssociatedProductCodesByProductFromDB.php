<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity\Query;

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductCodesByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

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
    public function getCodes(int $productId, AssociationInterface $association)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $userGroupsIds = $user->getGroupsIds();

        $associationTable = $association instanceof ProductModelAssociationInterface
            ? 'pim_catalog_product_model_association'
            : 'pim_catalog_association';
        $associationProductTable = $association instanceof ProductModelAssociationInterface
            ? 'pim_catalog_association_product_model_to_product'
            : 'pim_catalog_association_product';

        $sql = <<<SQL
SELECT DISTINCT(p.identifier) as code
FROM $associationTable a
    INNER JOIN $associationProductTable ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product p ON p.id = ap.product_id
    LEFT JOIN pim_catalog_category_product cp on p.id = cp.product_id
    LEFT JOIN pimee_security_product_category_access pca ON pca.category_id = cp.category_id AND pca.user_group_id IN (:userGroupsIds)
WHERE a.owner_id = :ownerId AND a.association_type_id = :associationTypeId
    AND (cp.category_id IS NULL OR pca.view_items = 1)
ORDER BY p.identifier ASC;
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'userGroupsIds'     => $userGroupsIds,
                'ownerId'           => $productId,
                'associationTypeId' => $association->getAssociationType()->getId()
            ],
            [
                'userGroupsIds'     => Connection::PARAM_INT_ARRAY,
                'ownerId'           => \PDO:: PARAM_INT,
                'associationTypeId' => \PDO:: PARAM_INT
            ]
        );

        $codes = array_map(function ($row) {
            return $row['code'];
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        return $codes;
    }
}
