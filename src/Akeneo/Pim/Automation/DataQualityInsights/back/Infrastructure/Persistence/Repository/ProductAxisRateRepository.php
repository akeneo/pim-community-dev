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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Example of a JSON string stored in the column "rates":
 * {
 *    "mobile": {
 *      "en_US": {
 *        "rank": 1,
 *        "rate": 96
 *      },
 *      "fr_FR": {
 *        "rank": 5,
 *        "rate": 36
 *      }
 *    },
 *    "ecommerce": {
 *      "en_US": {
 *        "rank": 2,
 *        "rate": 82
 *      },
 *      "fr_FR": {
 *        "rank": 5,
 *        "rate": 32
 *      }
 *    }
 *  }
 */
final class ProductAxisRateRepository implements ProductAxisRateRepositoryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function save(array $productAxisRates)
    {
        $valuesPlaceholders = implode(',', array_fill(0, count($productAxisRates), '(?, ?, ?, ?)'));

        $sql = <<<SQL
REPLACE INTO pimee_data_quality_insights_product_axis_rates (axis_code, product_id, evaluated_at, rates)
VALUES $valuesPlaceholders;
SQL;

        $statement = $this->db->prepare($sql);
        $valuePlaceholderIndex = 1;
        foreach ($productAxisRates as $item) {
            $statement->bindValue($valuePlaceholderIndex++, $item['axis']);
            $statement->bindValue($valuePlaceholderIndex++, strval($item['product_id']));
            $statement->bindValue($valuePlaceholderIndex++, $item['evaluated_at']->format('Y-m-d'));
            $statement->bindValue($valuePlaceholderIndex++, json_encode($item['rates']));
        }
        $statement->execute();
    }
}
