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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Doctrine;

use Doctrine\DBAL\Connection;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductSubscriptionCountQuery
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connexion
     */
    public function __construct(Connection $connexion)
    {
        $this->connection = $connexion;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): int
    {
        $sql = <<<SQL
SELECT COUNT(1) AS product_subscription_count from pim_suggest_data_product_subscription;
SQL;
        $statement = $this->connection->executeQuery($sql);
        $result = $statement->fetch();

        return intval($result['product_subscription_count']);
    }
}
