<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistribution;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

final class GetCatalogProductScoreEvolution
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function byCatalog(ChannelCode $channel, LocaleCode $locale): array
    {
        $sql = <<<'SQL'
SELECT scores
FROM pim_data_quality_insights_dashboard_scores_projection
WHERE type = :type
SQL;

        $stmt = $this->db->executeQuery($sql, ['type' => DashboardProjectionType::CATALOG]);

        return $this->buildResult($stmt, (string) $channel, (string) $locale);
    }

    public function byCategory(ChannelCode $channel, LocaleCode $locale, CategoryCode $category): array
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

        return $this->buildResult($stmt, (string) $channel, (string) $locale);
    }

    public function byFamily(ChannelCode $channel, LocaleCode $locale, FamilyCode $family): array
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

        return $this->buildResult($stmt, (string) $channel, (string) $locale);
    }

    private function buildResult(ResultStatement $stmt, string $channel, string $locale): array
    {
        $productScoreEvolution = [
            'average_rank' => null,
            'data' => [],
        ];

        $result = $stmt->fetchColumn(0);
        if ($result === null || $result === false) {
            return $productScoreEvolution;
        }

        $scores = json_decode($result, true);
        if (empty($scores)) {
            return $productScoreEvolution;
        }

        $monthlyTimePeriodDateFormat = (new ConsolidationDate(new \DateTimeImmutable()))->isLastDayOfMonth() ?
            new \DateTimeImmutable() :
            (new \DateTimeImmutable())->setTimestamp(strtotime(date('Y-m-t')));

        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $newDate = $monthlyTimePeriodDateFormat->modify('last day of '.$i.' month ago');
            $data[$newDate->format('Y-m-d')] = null;
        }

        foreach (array_keys($data) as $period) {
            if (isset($scores['monthly'][$period][$channel][$locale])) {
                $ranksDistribution = new RanksDistribution($scores['monthly'][$period][$channel][$locale]);
                $data[$period] = $ranksDistribution->getAverageRank()->toLetter();
            }
        }

        $currentAverageRank = isset($scores['average_ranks'][$channel][$locale]) ? (Rank::fromString($scores['average_ranks'][$channel][$locale]))->toLetter() : null;

        $data[array_key_last($data)] = $currentAverageRank;

        $productScoreEvolution['average_rank'] = $currentAverageRank;
        $productScoreEvolution['data'] = $data;

        return $productScoreEvolution;
    }
}
