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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
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

    public function byProductId(ProductId $productId): AxisRateCollection
    {
        $query = <<<SQL
SELECT product_id, JSON_OBJECTAGG(axis_code, rates) AS rates
FROM (
    SELECT latest_eval.axis_code, latest_eval.product_id, latest_eval.rates
    FROM pimee_data_quality_insights_product_axis_rates AS latest_eval
        LEFT JOIN pimee_data_quality_insights_product_axis_rates AS other_eval
            ON other_eval.axis_code = latest_eval.axis_code
            AND other_eval.product_id = latest_eval.product_id
            AND latest_eval.evaluated_at < other_eval.evaluated_at
    WHERE latest_eval.product_id = :product_id
        AND other_eval.evaluated_at IS NULL
) latest_product_rates
GROUP BY product_id
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            ['product_id' => $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        );

        $axesRates = new AxisRateCollection();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (false === $result) {
            return $axesRates;
        }

        $rawAxesRates = json_decode($result['rates'], true);
        foreach ($rawAxesRates as $axis => $rawRates) {
            $axisRates = ChannelLocaleRateCollection::fromNormalizedRates($rawRates, function ($rawRate) {
                return $rawRate['value'];
            });
            $axesRates->add(new AxisCode($axis), $axisRates);
        }

        return $axesRates;
    }
}
