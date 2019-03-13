<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductSubscriptionsExistQuery implements ProductSubscriptionsExistQueryInterface
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
     * @param array $productIds
     *
     * @return array
     */
    public function execute(array $productIds): array
    {
        $sql = <<<SQL
SELECT product_id
FROM pimee_franklin_insights_subscription 
WHERE product_id IN(:product_ids);
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            ['product_ids' => $productIds],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $productSubscriptionsExist = array_fill_keys($productIds, false);

        foreach ($result as $subscription) {
            $productSubscriptionsExist[$subscription['product_id']] = true;
        }

        return $productSubscriptionsExist;
    }
}
