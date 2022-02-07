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

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

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
        SELECT
            p.identifier, IF(COUNT(c.code) = 0, JSON_ARRAY(), JSON_ARRAYAGG(c.code)) AS category_codes
        FROM pim_catalog_product p
            LEFT JOIN pim_catalog_category_product cp ON cp.product_id = p.id
            LEFT JOIN pim_catalog_category c ON c.id = cp.category_id
        WHERE p.identifier IN (:product_identifiers)
        GROUP BY p.identifier
        SQL;

        $results = $this->connection->executeQuery(
            $sql,
            ['product_identifiers' => $stringProductIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $indexedResults = [];
        foreach ($results as $result) {
            $indexedResults[$result['identifier']] = \json_decode($result['category_codes'], true);
        }

        return $indexedResults;
    }
}
