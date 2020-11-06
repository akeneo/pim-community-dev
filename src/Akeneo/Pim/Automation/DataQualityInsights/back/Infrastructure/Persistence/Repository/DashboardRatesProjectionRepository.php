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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardPurgeDateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Example of a JSON string stored in the column "rates" of a projection entry:
 * {
 *    "daily": {
 *      "2019-12-19": {
 *        "enrichment": {
 *          "ecommerce": {
 *            "en_US": {
 *              "rank_1": 123,
 *              "rank_2": 321,
 *              "rank_3": 12
 *              "rank_4": 65
 *              "rank_5": 432
 *            },
 *            "fr_FR": {
 *              "rank_1": 123,
 *            }
 *          }
 *        },
 *        "consistency": {
 *          "print": {
 *            "en_US": {
 *              "rank_1": 123
 *            }
 *          }
 *        }
 *      }
 *    },
 *    "weekly": {
 *      "2019-12-28": {
 *        "enrichment": {
 *          "ecommerce": {
 *            "en_US": {
 *              "rank_3": 123,
 *              "rank_4": 35,
 *              "rank_5": 87
 *            }
 *          }
 *        },
 *        "consistency": {
 *          "ecommerce": {
 *            "en_US": {
 *              "rank_1": 123
 *            }
 *          }
 *        }
 *      }
 *    },
 *    "monthly": {
 *      "2019-12-31": {
 *        "enrichment": {
 *          "ecommerce": {
 *            "en_US": {
 *              "rank_3": 123,
 *              "rank_4": 345,
 *              "rank_5": 42
 *            }
 *          }
 *        },
 *        "consistency": {
 *          "print": {
 *            "en_US": {
 *              "rank_1": 123
 *            }
 *          }
 *        }
 *      }
 *    },
 *    "average_ranks": {
 *      "enrichment": {
 *        "en_US": "rank_2",
 *        "fr_FR": "rank_3"
 *      }
 *    },
 *    "average_ranks_consolidated_at" => "2020-01-24 14:42:35"
 *  }
 */
final class DashboardRatesProjectionRepository implements DashboardRatesProjectionRepositoryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(\Doctrine\DBAL\Driver\Connection $db)
    {
        $this->db = $db;
    }

    public function save(DashboardRatesProjection $ratesProjection): void
    {
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_dashboard_rates_projection (type, code, rates)
VALUES (:type, :code, :rates)
ON DUPLICATE KEY UPDATE rates = JSON_MERGE_PATCH(rates, :rates);
SQL;

        $this->db->executeQuery($query, [
            'type' => $ratesProjection->getType(),
            'code' => $ratesProjection->getCode(),
            'rates' => json_encode($ratesProjection->getRanksDistributionsPerTimePeriod())
        ]);

        $this->saveAverageRanks($ratesProjection);
    }

    public function purgeRates(DashboardPurgeDateCollection $purgeDates): void
    {
        $pathsToRemove = [];

        /** @var Write\DashboardPurgeDate $purgeDate */
        foreach ($purgeDates as $purgeDate) {
            $pathsToRemove[] = sprintf('\'$."%s"."%s"\'', strval($purgeDate->getPeriod()), $purgeDate->getDate()->format());
        }

        if (empty($pathsToRemove)) {
            return;
        }

        $pathsToRemove = implode(', ', $pathsToRemove);

        $query = <<<SQL
UPDATE pim_data_quality_insights_dashboard_rates_projection
SET rates = JSON_REMOVE(rates, $pathsToRemove)
SQL;

        $this->db->executeQuery($query);
    }

    private function saveAverageRanks(DashboardRatesProjection $ratesProjection): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_dashboard_rates_projection
SET rates = JSON_MERGE_PATCH(rates, :rates)
WHERE type = :type AND code = :code 
  AND (
      NOT JSON_CONTAINS_PATH(rates, 'one', '$.average_ranks_consolidated_at')
      OR JSON_UNQUOTE(JSON_EXTRACT(rates, '$.average_ranks_consolidated_at')) < :consolidated_at
  );
SQL;
        $rates = [
            'average_ranks' => $ratesProjection->getAverageRanks(),
            'average_ranks_consolidated_at' => $ratesProjection->getConsolidationDate()->format('Y-m-d H:i:s')
        ];

        $this->db->executeQuery($query, [
            'type' => $ratesProjection->getType(),
            'code' => $ratesProjection->getCode(),
            'rates' => json_encode($rates),
            'consolidated_at' => $ratesProjection->getConsolidationDate()->format('Y-m-d H:i:s')
        ]);
    }
}
