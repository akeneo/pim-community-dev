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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

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
 *      "2019-50": {
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
 *      "2019-12": {
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
 *    }
 *  }
 */
final class DashboardRatesProjectionRepository implements DashboardRatesProjectionRepositoryInterface
{
    public const TYPE_CATALOG_PROJECTION = 'catalog';
    public const TYPE_CATEGORY_PROJECTION = 'category';
    public const TYPE_FAMILY_PROJECTION = 'family';

    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function findCatalogProjection(ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity): ?Read\DashboardRates
    {
        $sql = <<<'SQL'
SELECT rates
FROM pimee_data_quality_insights_dashboard_rates_projection
WHERE type = :type
SQL;

        $stmt = $this->db->executeQuery($sql, ['type' => self::TYPE_CATALOG_PROJECTION]);

        return $this->buildResult($stmt, $channel, $locale, $periodicity);
    }

    public function findCategoryProjection(ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity, CategoryCode $category): ?Read\DashboardRates
    {
        $sql = <<<'SQL'
SELECT rates
FROM pimee_data_quality_insights_dashboard_rates_projection
WHERE type = :type
AND code = :code
SQL;

        $stmt = $this->db->executeQuery($sql, [
            'type' => self::TYPE_CATEGORY_PROJECTION,
            'code' => $category,
        ]);

        return $this->buildResult($stmt, $channel, $locale, $periodicity);
    }

    public function findFamilyProjection(ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity, FamilyCode $family): ?Read\DashboardRates
    {
        $sql = <<<'SQL'
SELECT rates
FROM pimee_data_quality_insights_dashboard_rates_projection
WHERE type = :type
AND code = :code
SQL;

        $stmt = $this->db->executeQuery($sql, [
            'type' => self::TYPE_FAMILY_PROJECTION,
            'code' => $family,
        ]);

        return $this->buildResult($stmt, $channel, $locale, $periodicity);
    }

    private function buildResult(ResultStatement $stmt, ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity): ?Read\DashboardRates
    {
        $result = $stmt->fetchColumn(0);
        if ($result === null || $result === false) {
            return null;
        }

        return new Read\DashboardRates(json_decode($result, true), $channel, $locale, $periodicity);
    }

    public function save(Write\DashboardRatesProjection $ratesProjection): void
    {
        $query = <<<SQL
INSERT INTO pimee_data_quality_insights_dashboard_rates_projection (type, code, rates)
VALUES (:type, :code, :rates)
ON DUPLICATE KEY UPDATE rates = JSON_MERGE_PATCH(rates, :rates);
SQL;

        $this->db->executeQuery($query, [
            'type' => $ratesProjection->getType(),
            'code' => $ratesProjection->getCode(),
            'rates' => json_encode($ratesProjection->getRates())
        ]);
    }

    private function getDateFormatByPeriodicity(Periodicity $periodicity): string
    {
        switch (strval($periodicity)) {
            case Periodicity::DAILY:
                return 'Y-m-d';
            case Periodicity::WEEKLY:
                return 'Y-W';
            case Periodicity::MONTHLY:
                return 'Y-m';
            default:
                throw new \InvalidArgumentException(sprintf('The periodicity %s is not supported', $periodicity));
        }
    }
}
