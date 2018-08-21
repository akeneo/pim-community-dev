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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Query\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Query\GetSubscriptionStatusForProductInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * Checks that a product subscription to Franklin exists in database for a given product ID.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSubscriptionStatusForProduct implements GetSubscriptionStatusForProductInterface
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
    public function query(int $productId): bool
    {
        $query = <<<SQL
SELECT EXISTS (
    SELECT 1
    FROM pim_suggest_data_product_subscription
    WHERE product_id = :product_id
) as is_existing
SQL;
        $statement = $this->connection->executeQuery($query, ['product_id' => $productId]);

        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }
}
