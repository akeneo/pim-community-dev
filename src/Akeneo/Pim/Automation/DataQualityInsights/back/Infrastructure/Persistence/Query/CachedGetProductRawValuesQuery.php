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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductRawValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

class CachedGetProductRawValuesQuery implements GetProductRawValuesQueryInterface
{
    /** * @var Connection */
    private $db;

    /** @var null|int */
    private $cachedProductId;

    /** @var array */
    private $cachedProductValues = [];

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductId $productId): array
    {
        $productId = $productId->toInt();
        if ($productId === $this->cachedProductId) {
            return $this->cachedProductValues;
        }

        $query = <<<SQL
SELECT raw_values FROM pim_catalog_product WHERE id = :product_id;
SQL;

        $statement = $this->db->executeQuery($query,
            [
                'product_id' => $productId,
            ],
            [
                'product_id' => \PDO::PARAM_INT,
            ]
        );

        $result = $statement->fetchColumn();
        $rawValues = false === $result ? [] : json_decode($result, true);

        $this->cachedProductId = $productId;
        $this->cachedProductValues = $rawValues;

        return $rawValues;
    }
}
