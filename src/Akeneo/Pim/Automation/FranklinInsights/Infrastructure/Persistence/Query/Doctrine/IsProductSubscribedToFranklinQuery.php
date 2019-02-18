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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\IsProductSubscribedToFranklinQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IsProductSubscribedToFranklinQuery implements IsProductSubscribedToFranklinQueryInterface
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
     * @param int $productId
     *
     * @return bool
     */
    public function execute(int $productId): bool
    {
        $sql = <<<SQL
SELECT 1 as is_product_subscribed
FROM pimee_franklin_insights_subscription 
WHERE product_id = :product_id;
SQL;

        $bindParams = [
            'product_id' => $productId,
        ];

        $statement = $this->connection->executeQuery($sql, $bindParams);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (false === $result) {
            return false;
        }

        return (bool) $result['is_product_subscribed'];
    }
}
