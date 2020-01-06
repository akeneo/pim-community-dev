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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductRawValuesByAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

class GetProductRawValuesByAttributeQuery implements GetProductRawValuesByAttributeQueryInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(ProductId $productId, array $attributeCodes): array
    {
        $query = <<<SQL
SELECT raw_values FROM pim_catalog_product WHERE id = :product_id;
SQL;

        $statement = $this->db->executeQuery($query,
            [
                'product_id' => $productId->toInt(),
            ],
            [
                'product_id' => \PDO::PARAM_INT,
            ]
        );

        $rawValues = $statement->fetchColumn();

        return array_filter(json_decode($rawValues, true), function (string $attributeCode) use ($attributeCodes) {
            return in_array($attributeCode, $attributeCodes);
        }, ARRAY_FILTER_USE_KEY);
    }
}
