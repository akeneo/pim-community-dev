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

use Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductUuidsByProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class GetAssociatedProductUuidsByProductFromDB implements GetAssociatedProductUuidsByProduct
{
    public function __construct(private Connection $connection, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUuids(UuidInterface $productUuid, AssociationInterface $association): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $userGroupsIds = $user->getGroupsIds();

        $sql = <<<SQL
SELECT DISTINCT(BIN_TO_UUID(p.uuid)) as uuid
FROM pim_catalog_association a
    INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product p ON p.uuid = ap.product_uuid
    LEFT JOIN pim_catalog_category_product cp on p.uuid = cp.product_uuid
    LEFT JOIN pimee_security_product_category_access pca ON pca.category_id = cp.category_id AND pca.user_group_id IN (:userGroupsIds)
WHERE a.owner_uuid = UUID_TO_BIN(:ownerUuid) AND a.association_type_id = :associationTypeId
    AND (cp.category_id IS NULL OR pca.view_items = 1);
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'userGroupsIds' => $userGroupsIds,
                'ownerUuid' => $productUuid->toString(),
                'associationTypeId' => $association->getAssociationType()->getId(),
            ],
            [
                'userGroupsIds' => Connection::PARAM_INT_ARRAY,
            ]
        );

        $uuids = array_map(function ($row) {
            return $row['uuid'];
        }, $stmt->fetchAllAssociative());

        return $uuids;
    }
}
