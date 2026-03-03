<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCategoryCodes implements GetCategoryCodes
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductUuids(array $uuids): array
    {
        if ([] === $uuids) {
            return [];
        }

        Assert::allIsInstanceOf($uuids, UuidInterface::class);
        $productUuidsAsBytes = \array_map(
            static fn (UuidInterface $uuid): string => $uuid->getBytes(),
            $uuids
        );

        $sql = <<<SQL
        WITH
        existing_product AS (
            SELECT uuid, product_model_id FROM pim_catalog_product WHERE uuid IN (:product_uuids)
        )
        SELECT BIN_TO_UUID(p.uuid) AS uuid, IF(COUNT(mc.category_code) = 0, JSON_ARRAY(), JSON_ARRAYAGG(mc.category_code)) as category_codes
        FROM 
            existing_product p
            LEFT JOIN (
                SELECT
                    p.uuid, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
                    INNER JOIN pim_catalog_category c ON c.id = cp.category_id
                UNION ALL
                SELECT
                    p.uuid, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                    INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = sub.id
                    INNER JOIN pim_catalog_category c ON c.id = cpm.category_id
                UNION ALL
                SELECT
                    p.uuid, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                    INNER JOIN pim_catalog_product_model root ON root.id = sub.parent_id
                    INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = root.id
                    INNER JOIN pim_catalog_category c ON c.id = cpm.category_id
            ) AS mc ON mc.uuid = p.uuid
        GROUP BY p.uuid
        SQL;

        $results = $this->connection->executeQuery(
            $sql,
            ['product_uuids' => $productUuidsAsBytes],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $indexedResults = [];
        foreach ($results as $result) {
            $indexedResults[(string) $result['uuid']] = \json_decode($result['category_codes'], true);
        }

        return $indexedResults;
    }
}
