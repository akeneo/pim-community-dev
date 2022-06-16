<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Doctrine\DBAL\Connection;
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

        $productCategories = $this->getProductCategoriesFromProductIdentifiers($stringProductIdentifiers);
        $productModelCategories = $this->getProductModelCategoriesFromProductIdentifiers($stringProductIdentifiers);

        $indexedResults = [];
        foreach (\array_keys(\array_merge($productModelCategories, $productCategories)) as $productIdentifier) {
            $productModelCategoryList = $productModelCategories[$productIdentifier] ?? [];
            $productCategoryList = $productCategories[$productIdentifier] ?? [];
            $indexedResults[(string) $productIdentifier] = \array_values(\array_merge($productModelCategoryList, $productCategoryList));
        }

        return $indexedResults;
    }

    /**
     * @param string[] $productIdentifiers
     * @return array<string, string[]> example:
     *  {
     *      "product1": ["categoryA", "categoryB"],
     *      "product2": ["categoryA"],
     *      ...
     *  }
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getProductModelCategoriesFromProductIdentifiers(array $productIdentifiers): array
    {
        $sql = <<<SQL
        WITH
        existing_product AS (
            SELECT id, product_model_id, identifier FROM pim_catalog_product WHERE identifier IN (:product_identifiers)
        )
        SELECT p.identifier, IF(COUNT(mc.category_code) = 0, JSON_ARRAY(), JSON_ARRAYAGG(mc.category_code)) as category_codes
        FROM 
            existing_product p
            LEFT JOIN (
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
            ['product_identifiers' => $productIdentifiers],
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

    /**
     * @param string[] $productIdentifiers
     * @return array<string, string[]> example:
     *  {
     *      "product1": ["categoryA", "categoryB"],
     *      "product2": ["categoryA"],
     *      ...
     *  }
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getProductCategoriesFromProductIdentifiers(array $productIdentifiers): array
    {
        $sql = <<<SQL
        WITH
            existing_product AS (
                SELECT id, identifier FROM pim_catalog_product WHERE identifier IN (:product_identifiers)
            )
        SELECT p.identifier, IF(COUNT(mc.category_code) = 0, JSON_ARRAY(), JSON_ARRAYAGG(mc.category_code)) as category_codes
        FROM
            existing_product p
                LEFT JOIN (
                SELECT
                    p.identifier, c.code AS category_code
                FROM existing_product p
                    INNER JOIN pim_catalog_category_product cp ON cp.product_id = p.id
                    INNER JOIN pim_catalog_category c ON c.id = cp.category_id
            ) AS mc ON mc.identifier = p.identifier
        GROUP BY p.identifier
        SQL;

        $results = $this->connection->executeQuery(
            $sql,
            ['product_identifiers' => $productIdentifiers],
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
}
