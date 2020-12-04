<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Example of a JSON string stored in the column "scores" of a projection entry:
 * {
 *    "daily": {
 *      "2019-12-19": {
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
 *          },
 *          "print": {
 *            "en_US": {
 *              "rank_1": 123
 *            }
 *          }
 *      }
 *    },
 *    "weekly": {
 *      "2019-12-28": {
 *          "ecommerce": {
 *            "en_US": {
 *              "rank_3": 123,
 *              "rank_4": 35,
 *              "rank_5": 87
 *            }
 *          }
 *      }
 *    },
 *    "monthly": {
 *      "2019-12-31": {
 *          "ecommerce": {
 *            "en_US": {
 *              "rank_3": 123,
 *              "rank_4": 345,
 *              "rank_5": 42
 *            }
 *          },
 *          "print": {
 *            "en_US": {
 *              "rank_1": 123
 *            }
 *          }
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
final class DashboardScoresProjectionRepository implements DashboardScoresProjectionRepositoryInterface
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function save(Write\DashboardRatesProjection $ratesProjection): void
    {
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_dashboard_scores_projection (type, code, scores)
VALUES (:type, :code, :scores)
ON DUPLICATE KEY UPDATE scores = JSON_MERGE_PATCH(scores, :scores);
SQL;

        $this->db->executeQuery($query, [
            'type' => $ratesProjection->getType(),
            'code' => $ratesProjection->getCode(),
            'scores' => json_encode($ratesProjection->getRanksDistributionsPerTimePeriod())
        ]);

        $this->saveAverageRanks($ratesProjection);
    }

    public function purgeRates(Write\DashboardPurgeDateCollection $purgeDates): void
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
UPDATE pim_data_quality_insights_dashboard_scores_projection
SET scores = JSON_REMOVE(scores, $pathsToRemove)
SQL;

        $this->db->executeQuery($query);
    }

    private function saveAverageRanks(Write\DashboardRatesProjection $ratesProjection): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_dashboard_scores_projection
SET scores = JSON_MERGE_PATCH(scores, :scores)
WHERE type = :type AND code = :code 
  AND (
      NOT JSON_CONTAINS_PATH(scores, 'one', '$.average_ranks_consolidated_at')
      OR JSON_UNQUOTE(JSON_EXTRACT(scores, '$.average_ranks_consolidated_at')) < :consolidated_at
  );
SQL;
        $scores = [
            'average_ranks' => $ratesProjection->getAverageRanks(),
            'average_ranks_consolidated_at' => $ratesProjection->getConsolidationDate()->format('Y-m-d H:i:s')
        ];

        $this->db->executeQuery($query, [
            'type' => $ratesProjection->getType(),
            'code' => $ratesProjection->getCode(),
            'scores' => json_encode($scores),
            'consolidated_at' => $ratesProjection->getConsolidationDate()->format('Y-m-d H:i:s')
        ]);
    }
}
