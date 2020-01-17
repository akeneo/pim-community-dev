<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class GetLatestProductAxesRatesQuery implements GetLatestProductAxesRatesQueryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function byProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $productIds = array_map(function (ProductId $productId) {
            return $productId->toInt();
        }, $productIds);

        $query = <<<SQL
SELECT product_id, JSON_OBJECTAGG(axis_code, rates) AS rates
FROM (
    SELECT latest_eval.axis_code, latest_eval.product_id, latest_eval.rates
    FROM pimee_data_quality_insights_product_axis_rates AS latest_eval
        LEFT JOIN pimee_data_quality_insights_product_axis_rates AS other_eval
            ON other_eval.axis_code = latest_eval.axis_code
            AND other_eval.product_id = latest_eval.product_id
            AND latest_eval.evaluated_at < other_eval.evaluated_at
    WHERE latest_eval.product_id IN(:product_ids)
        AND other_eval.evaluated_at IS NULL
) latest_product_rates
GROUP BY product_id
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            ['product_ids' => $productIds],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        );

        $productAxesRates = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productId = intval($row['product_id']);
            $axesRates = json_decode($row['rates'], true);
            $productAxesRates[$productId] = new ProductAxesRates(new ProductId($productId), $axesRates);
        }

        return $productAxesRates;
    }
}
