<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
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
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if ([] === $productIdentifiers) {
            return [];
        }

        Assert::allIsInstanceOf($productIdentifiers, ProductIdentifier::class);
        $stringProductIdentifiers = \array_map(
            static fn (ProductIdentifier $productIdentifier): string => $productIdentifier->asString(),
            $productIdentifiers
        );

        $sql = <<<SQL
        WITH
        existing_product AS (
            SELECT uuid, product_model_id, identifier FROM pim_catalog_product WHERE identifier IN (:product_identifiers)
        )
        SELECT p.identifier, IF(COUNT(mc.category_code) = 0, JSON_ARRAY(), JSON_ARRAYAGG(mc.category_code)) as category_codes
        FROM 
            existing_product p
            LEFT JOIN (
                SELECT
                    p.identifier, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
                    INNER JOIN pim_catalog_category c ON c.id = cp.category_id
                UNION ALL
                SELECT
                    p.identifier, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                    INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = sub.id
                    INNER JOIN pim_catalog_category c ON c.id = cpm.category_id
                UNION ALL
                SELECT
                    p.identifier, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_product_model sub ON sub.id = p.product_model_id
                    INNER JOIN pim_catalog_product_model root ON root.id = sub.parent_id
                    INNER JOIN pim_catalog_category_product_model cpm ON cpm.product_model_id = root.id
                    INNER JOIN pim_catalog_category c ON c.id = cpm.category_id
            ) AS mc ON mc.identifier = p.identifier
        GROUP BY p.identifier
        SQL;

        $results = $this->connection->executeQuery(
            $sql,
            ['product_identifiers' => $stringProductIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $indexedResults = [];
        foreach ($results as $result) {
            /** @var string[] $decoded */
            $decoded = \json_decode($result['category_codes'], true);
            $indexedResults[(string) $result['identifier']] = $decoded;
        }

        return $indexedResults;
    }

    public function fromProductUuids(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        Assert::allIsInstanceOf($productUuids, UuidInterface::class);

        $uuidsAsBytes = array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids);

        $results = [];
        foreach ($productUuids as $uuid) {
            $results[$uuid->toString()] = [];
        }

        $forProductQuery = <<<SQL
SELECT product_uuid, JSON_ARRAYAGG(category_codes) as category_codes
FROM (
         SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_category_product category_product ON product.uuid = category_product.product_uuid
                INNER JOIN pim_catalog_category category ON category.id = category_product.category_id
         WHERE product.uuid IN (?)
       UNION ALL
         SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
                INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
                INNER JOIN pim_catalog_category_product_model category_model ON model.id = category_model.product_model_id
                INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.uuid IN (?)
       UNION ALL
         SELECT BIN_TO_UUID(product.uuid) as product_uuid, category.code as category_codes
         FROM pim_catalog_product product
           INNER JOIN pim_catalog_product_model model ON product.product_model_id = model.id
           INNER JOIN pim_catalog_product_model parent ON parent.id = model.parent_id
           INNER JOIN pim_catalog_category_product_model category_model ON parent.id = category_model.product_model_id
           INNER JOIN pim_catalog_category category ON category_model.category_id = category.id
         WHERE product.uuid IN (?)
) all_results
GROUP BY product_uuid
SQL;

        $queryResults = $this->connection->fetchAllAssociative(
            $forProductQuery,
            [$uuidsAsBytes, $uuidsAsBytes, $uuidsAsBytes],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        foreach ($queryResults as $queryResult) {
            $categoryCodes = json_decode($queryResult['category_codes']);
            sort($categoryCodes);
            $categoryCodes = array_values(array_unique($categoryCodes));
            $results[(string) $queryResult['product_uuid']] = $categoryCodes;
        }

        return $results;
    }
}
