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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRankCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class GetLatestProductAxesRanksQuery implements GetLatestProductAxesRanksQueryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(\Doctrine\DBAL\Driver\Connection $db)
    {
        $this->db = $db;
    }

    public function byProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $productIds = array_map(fn(ProductId $productId) => $productId->toInt(), $productIds);

        $query = <<<SQL
SELECT product_id, JSON_OBJECTAGG(axis_code, rates) AS rates
FROM (
    SELECT latest_eval.axis_code, latest_eval.product_id, latest_eval.rates
    FROM pim_data_quality_insights_product_axis_rates AS latest_eval
        LEFT JOIN pim_data_quality_insights_product_axis_rates AS other_eval
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

        $productAxesRanks = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productId = (int) $row['product_id'];
            $axesRanks = json_decode($row['rates'], true);
            $productAxesRanks[$productId] = $this->hydrateAxesRanks($axesRanks);
        }

        return $productAxesRanks;
    }

    private function hydrateAxesRanks(array $rawAxesRanks): AxisRankCollection
    {
        $axesRanks = new AxisRankCollection();
        foreach ($rawAxesRanks as $axis => $rawRanks) {
            $axisRanks = ChannelLocaleRankCollection::fromNormalizedRanks($rawRanks, fn($rawRank) => $rawRank['rank']);
            $axesRanks->add(new AxisCode($axis), $axisRanks);
        }

        return $axesRanks;
    }
}
