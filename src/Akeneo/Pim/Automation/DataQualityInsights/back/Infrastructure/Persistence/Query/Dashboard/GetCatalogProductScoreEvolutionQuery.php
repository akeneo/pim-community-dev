<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CatalogQualityScoreEvolution;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetCatalogProductScoreEvolutionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;

final class GetCatalogProductScoreEvolutionQuery implements GetCatalogProductScoreEvolutionQueryInterface
{
    private Connection $db;

    private Clock $clock;

    public function __construct(Connection $db, Clock $clock)
    {
        $this->db = $db;
        $this->clock = $clock;
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
        if (empty($scores) || !array_key_exists('monthly', $scores)) {
            return $productScoreEvolution;
        }

        return (new CatalogQualityScoreEvolution($this->clock->getCurrentTime(), $scores, $channel, $locale))->toArray();
    }
}
