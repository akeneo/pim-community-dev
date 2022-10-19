<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Permission\Component\Authorization\FetchUserRightsOnProductInterface;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductUuid;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class FetchUserRightsOnProduct implements FetchUserRightsOnProductInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchByIdentifier(string $productIdentifier, int $userId): UserRightsOnProduct
    {
        $rights = $this->fetchByIdentifiers([$productIdentifier], $userId);
        if (empty($rights)) {
            throw new ObjectNotFoundException(
                sprintf('UserRights does not exist for product identifier "%s" user id "%s".', $productIdentifier, $userId)
            );
        }

        return $this->fetchByIdentifiers([$productIdentifier], $userId)[0];
    }

    public function fetchByIdentifiers(array $productIdentifiers, int $userId): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $sql = <<<SQL
            SELECT 
                product_categories.product_identifier as product_identifier, 
                COALESCE(SUM(access.edit_items), 0) as count_editable_categories, 
                COALESCE(SUM(access.own_items), 0) as count_ownable_categories,
                COALESCE(SUM(access.view_items), 0) as count_viewable_categories,
                COUNT(product_categories.category_id) as number_categories
            FROM
                (
                    SELECT
                        p.identifier as product_identifier, cp.category_id
                    FROM 
                        pim_catalog_product p
                        LEFT JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
                        WHERE p.identifier IN (:productIdentifiers)
                    UNION
                    SELECT
                        p.identifier as product_identifier, cp.category_id
                    FROM 
                        pim_catalog_product p
                        JOIN pim_catalog_product_model pm ON pm.id = p.product_model_id
                        JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm.id
                        WHERE p.identifier IN (:productIdentifiers)
                    UNION
                    SELECT
                        p.identifier as product_identifier, cp.category_id
                    FROM 
                        pim_catalog_product p
                        JOIN pim_catalog_product_model pm1 ON pm1.id = p.product_model_id
                        JOIN pim_catalog_product_model pm2 on pm2.id = pm1.parent_id
                        JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm2.id
                    WHERE p.identifier IN (:productIdentifiers)
                ) as product_categories
                LEFT JOIN 
                 (
                    SELECT
                        pca.category_id,
                        pca.edit_items, 
                        pca.own_items,
                        pca.view_items
                    FROM pimee_security_product_category_access pca 
                    JOIN oro_access_group ag ON pca.user_group_id = ag.id
                    JOIN oro_user_access_group uag ON uag.group_id = ag.id AND uag.user_id = :user_id
                ) access ON access.category_id = product_categories.category_id
            GROUP BY 
                product_categories.product_identifier
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['productIdentifiers' => $productIdentifiers, 'user_id' =>$userId],
            ['productIdentifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return array_map(function (array $row) use ($userId) {
            return new UserRightsOnProduct(
                (string) $row['product_identifier'],
                $userId,
                (int) $row['count_editable_categories'],
                (int) $row['count_ownable_categories'],
                (int) $row['count_viewable_categories'],
                (int) $row['number_categories']
            );
        }, $result);
    }

    public function fetchByUuid(UuidInterface $productUuid, int $userId): UserRightsOnProductUuid
    {
        $rights = $this->fetchByUuids([$productUuid], $userId);
        if (empty($rights)) {
            throw new ObjectNotFoundException(
                sprintf('UserRights does not exist for product uuid "%s" user id "%s".', $productUuid->toString(), $userId)
            );
        }

        return $rights[0];
    }

    public function fetchByUuids(array $productUuids, int $userId): array
    {
        if (empty($productUuids)) {
            return [];
        }

        $sql = <<<SQL
            SELECT 
                BIN_TO_UUID(product_categories.uuid) as uuid,
                COALESCE(SUM(access.edit_items), 0) as count_editable_categories, 
                COALESCE(SUM(access.own_items), 0) as count_ownable_categories,
                COALESCE(SUM(access.view_items), 0) as count_viewable_categories,
                COUNT(product_categories.category_id) as number_categories
            FROM
                (
                    SELECT
                        p.uuid, cp.category_id
                    FROM 
                        pim_catalog_product p
                        LEFT JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
                        WHERE p.uuid IN (:productUuids)
                    UNION
                    SELECT
                        p.uuid, cp.category_id
                    FROM 
                        pim_catalog_product p
                        JOIN pim_catalog_product_model pm ON pm.id = p.product_model_id
                        JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm.id
                        WHERE p.uuid IN (:productUuids)
                    UNION
                    SELECT
                        p.uuid, cp.category_id
                    FROM 
                        pim_catalog_product p
                        JOIN pim_catalog_product_model pm1 ON pm1.id = p.product_model_id
                        JOIN pim_catalog_product_model pm2 on pm2.id = pm1.parent_id
                        JOIN pim_catalog_category_product_model cp ON cp.product_model_id = pm2.id
                    WHERE p.uuid IN (:productUuids)
                ) as product_categories
                LEFT JOIN 
                 (
                    SELECT
                        pca.category_id,
                        pca.edit_items, 
                        pca.own_items,
                        pca.view_items
                    FROM pimee_security_product_category_access pca 
                    JOIN oro_access_group ag ON pca.user_group_id = ag.id
                    JOIN oro_user_access_group uag ON uag.group_id = ag.id AND uag.user_id = :user_id
                ) access ON access.category_id = product_categories.category_id
            GROUP BY 
                product_categories.uuid
            ORDER BY FIELD(product_categories.uuid, :productUuids)
SQL;

        $productUuidsAsBytes = array_map(
            fn (UuidInterface $productUuid) => $productUuid->getBytes(),
            $productUuids
        );

        $result = $this->connection->executeQuery(
            $sql,
            ['productUuids' => $productUuidsAsBytes, 'user_id' => $userId],
            ['productUuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return array_map(fn (array $row) => new UserRightsOnProductUuid(
            Uuid::fromString($row['uuid']),
            $userId,
            (int) $row['count_editable_categories'],
            (int) $row['count_ownable_categories'],
            (int) $row['count_viewable_categories'],
            (int) $row['number_categories']
        ), $result);
    }
}
