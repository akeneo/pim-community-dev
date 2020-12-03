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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetAverageRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Doctrine\DBAL\Connection;

final class GetAverageRanksQuery implements GetAverageRanksQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byFamilies(ChannelCode $channelCode, LocaleCode $localeCode, array $familyCodes): array
    {
        return $this->fetchByCodes($channelCode, $localeCode, DashboardProjectionType::FAMILY, $familyCodes);
    }

    public function byCategories(ChannelCode $channelCode, LocaleCode $localeCode, array $categoryCodes): array
    {
        return $this->fetchByCodes($channelCode, $localeCode, DashboardProjectionType::CATEGORY, $categoryCodes);
    }

    private function fetchByCodes(ChannelCode $channelCode, LocaleCode $localeCode, string $entityType, array $entityCodes): array
    {
        $path = sprintf('\'$.average_ranks."%s"."%s"\'', $channelCode, $localeCode);

        $query = <<<SQL
SELECT
    code,
    JSON_UNQUOTE(JSON_EXTRACT(scores, $path)) AS average_rank
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE type = :type AND code IN (:codes)
SQL;

        $stmt = $this->connection->executeQuery(
            $query,
            [
                'type' => $entityType,
                'codes' => array_map('strval', $entityCodes),
            ],
            [
                'type' => \PDO::PARAM_STR,
                'codes' => Connection::PARAM_STR_ARRAY
            ]
        );

        $averageRanks = [];
        while ($rawAverageRanks = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $averageRanks[$rawAverageRanks['code']] = null !== $rawAverageRanks['average_rank'] ? Rank::fromString($rawAverageRanks['average_rank']) : null;
        }

        $entityAverageRanks = [];
        foreach ($entityCodes as $entityCode) {
            $entityCode = strval($entityCode);
            $entityAverageRanks[$entityCode] = $averageRanks[$entityCode] ?? null;
        }

        return $entityAverageRanks;
    }
}
