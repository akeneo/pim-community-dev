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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductAxisRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
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

    /** @var string */
    private $tableName;

    public function __construct(Connection $db, string $tableName)
    {
        $this->db = $db;
        $this->tableName = $tableName;
    }

    /**
     * @param ProductAxisRates[] $productsAxesRates
     */
    public function save(array $productsAxesRates): void
    {
        if (empty($productsAxesRates)) {
            return;
        }

        $productAxisRateTable = $this->tableName;
        $valuesPlaceholders = implode(',', array_fill(0, count($productsAxesRates), '(?, ?, ?, ?)'));

        $sql = <<<SQL
REPLACE INTO $productAxisRateTable (axis_code, product_id, evaluated_at, rates)
VALUES $valuesPlaceholders;
SQL;

        $statement = $this->db->prepare($sql);
        $valuePlaceholderIndex = 1;
        foreach ($productsAxesRates as $productAxisRates) {
            $rates = $productAxisRates->getRates()->mapWith(function (Rate $rate) {
                return [
                    'rank' => Rank::fromRate($rate)->toInt(),
                    'value' => $rate->toInt(),
                ];
            });
            $statement->bindValue($valuePlaceholderIndex++, $productAxisRates->getAxisCode());
            $statement->bindValue($valuePlaceholderIndex++, $productAxisRates->getProductId()->toInt());
            $statement->bindValue($valuePlaceholderIndex++, $productAxisRates->getEvaluatedAt()->format('Y-m-d'));
            $statement->bindValue($valuePlaceholderIndex++, json_encode($rates));
        }
        $statement->execute();
    }

    public function purgeUntil(\DateTimeImmutable $date): void
    {
        $productAxisRateTable = $this->tableName;

        $query = <<<SQL
DELETE old_rates
FROM $productAxisRateTable AS old_rates
INNER JOIN $productAxisRateTable AS younger_rates
    ON younger_rates.product_id = old_rates.product_id
    AND younger_rates.axis_code = old_rates.axis_code
    AND younger_rates.evaluated_at > old_rates.evaluated_at
WHERE old_rates.evaluated_at < :purge_date;
SQL;

        $this->db->executeQuery(
            $query,
            ['purge_date' => $date->format('Y-m-d')]
        );
    }
}
