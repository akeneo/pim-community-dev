<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetDashboardScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

final class GetDashboardScoresQuery implements GetDashboardScoresQueryInterface
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function byCatalog(ChannelCode $channel, LocaleCode $locale, TimePeriod $timePeriod): ?Read\DashboardRates
    {
        $sql = <<<'SQL'
SELECT scores
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE type = :type
SQL;

        $stmt = $this->db->executeQuery($sql, ['type' => DashboardProjectionType::CATALOG]);

        return $this->buildResult($stmt, $channel, $locale, $timePeriod);
    }

    public function byCategory(ChannelCode $channel, LocaleCode $locale, TimePeriod $timePeriod, CategoryCode $category): ?Read\DashboardRates
    {
        $sql = <<<'SQL'
SELECT scores
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE type = :type
AND code = :code
SQL;

        $stmt = $this->db->executeQuery($sql, [
            'type' => DashboardProjectionType::CATEGORY,
            'code' => $category,
        ]);

        return $this->buildResult($stmt, $channel, $locale, $timePeriod);
    }

    public function byFamily(ChannelCode $channel, LocaleCode $locale, TimePeriod $timePeriod, FamilyCode $family): ?Read\DashboardRates
    {
        $sql = <<<'SQL'
SELECT scores
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE type = :type
AND code = :code
SQL;

        $stmt = $this->db->executeQuery($sql, [
            'type' => DashboardProjectionType::FAMILY,
            'code' => $family,
        ]);

        return $this->buildResult($stmt, $channel, $locale, $timePeriod);
    }

    private function buildResult(ResultStatement $stmt, ChannelCode $channel, LocaleCode $locale, TimePeriod $timePeriod): ?Read\DashboardRates
    {
        $result = $stmt->fetchColumn(0);
        if ($result === null || $result === false) {
            return null;
        }

        return new Read\DashboardRates(json_decode($result, true), $channel, $locale, $timePeriod);
    }
}
