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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Product;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\Product\SelectProductFamilyIdQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * Checks if a product has a family in the data stored in MySQL.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectProductFamilyIdQuery implements SelectProductFamilyIdQueryInterface
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
    public function execute(int $productId): ?int
    {
        $query = <<<SQL
SELECT family_id
FROM pim_catalog_product
WHERE id = :product_id 
SQL;
        $bindParams = ['product_id' => $productId];
        $statement = $this->connection->executeQuery($query, $bindParams);
        $result = $statement->fetch();

        return (null === $result['family_id']) ? null : (int) $result['family_id'];
    }
}
