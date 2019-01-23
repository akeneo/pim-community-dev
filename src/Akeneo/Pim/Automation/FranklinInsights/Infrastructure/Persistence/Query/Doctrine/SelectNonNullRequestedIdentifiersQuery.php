<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectNonNullRequestedIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class SelectNonNullRequestedIdentifiersQuery implements SelectNonNullRequestedIdentifiersQueryInterface
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

    /**
     * {@inheritdoc}
     */
    public function execute(array $franklinIdentifiers, int $searchAfter, int $limit): array
    {
        if (empty($franklinIdentifiers)) {
            return [];
        }

        $nonNullIdentifiers = array_map(
            function (string $identifier) {
                return sprintf('requested_%s IS NOT NULL', $identifier);
            },
            $franklinIdentifiers
        );

        $sql = <<<SQL
SELECT product_id, requested_asin, requested_upc, requested_brand, requested_mpn
FROM pimee_franklin_insights_subscription
WHERE (%s)
AND product_id > :searchAfter
ORDER BY product_id ASC
LIMIT 0, :limit;
SQL;

        $rows = $this->connection->executeQuery(
            sprintf($sql, implode(' OR ', $nonNullIdentifiers)),
            [
                'searchAfter' => $searchAfter,
                'limit' => $limit,
            ],
            [
                'searchAfter' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['product_id']] = array_filter(
                [
                    'asin' => $row['requested_asin'],
                    'upc' => $row['requested_upc'],
                    'brand' => $row['requested_brand'],
                    'mpn' => $row['requested_mpn'],
                ]
            );
        }

        return $result;
    }
}
