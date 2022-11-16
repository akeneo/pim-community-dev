<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Infrastructure\AntiCorruptionLayer;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class ACLGetCategoryCodesWithAncestors
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param UuidInterface[] $productUuids
     * @return array<string, CategoryCode[]>
     */
    public function forProductUuids(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        Assert::allIsInstanceOf($productUuids, UuidInterface::class);

        $sql = <<<SQL
WITH RECURSIVE
recursive_category AS (
    SELECT cp.category_id, c.code, c.parent_id, p.uuid as product_uuid
    FROM pim_catalog_product p
        LEFT JOIN pim_catalog_category_product cp ON cp.product_uuid = p.uuid
        LEFT JOIN pim_catalog_category c ON c.id = cp.category_id
    WHERE p.uuid IN (:product_uuids)

    UNION DISTINCT

    SELECT c.id as category_id, c.code, c.parent_id, rc.product_uuid
    FROM pim_catalog_category c
        INNER JOIN recursive_category rc ON rc.parent_id = c.id
)
SELECT BIN_TO_UUID(product_uuid), GROUP_CONCAT(code) AS category_codes
FROM recursive_category
GROUP BY product_uuid
SQL;
        /** @var array<string, string> $results */
        $results = $this->connection->executeQuery(
            $sql,
            ['product_uuids' => \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids)],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllKeyValue();

        return \array_map(
            fn (?string $collapsedCodes): array => \array_map(
                fn (string $stringCategoryCodes): CategoryCode => CategoryCode::fromString($stringCategoryCodes),
                null === $collapsedCodes ? [] : \explode(',', $collapsedCodes)
            ),
            $results
        );
    }
}
