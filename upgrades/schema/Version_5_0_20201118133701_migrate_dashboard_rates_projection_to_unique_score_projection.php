<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20201118133701_migrate_dashboard_rates_projection_to_unique_score_projection extends AbstractMigration implements ContainerAwareInterface
{
    const BULK_SIZE = 10;

    private ?ContainerInterface $container;

    private ?Connection $db;

    private ?array $cachedChannelLocaleArray;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $this->db = $this->container->get('database_connection');
        $this->cachedChannelLocaleArray = null;

        foreach ($this->getLinesToMigrate(self::BULK_SIZE) as $linesToMigrate) {
            $this->migrateLines($linesToMigrate);
        }
    }

    private function migrateLines(array $linesToMigrate): void
    {
        $dashboardProjections = [];
        foreach ($linesToMigrate as $line) {
            $rates = json_decode($line['rates'], true);
            $scores = [];
            foreach (['daily', 'weekly', 'monthly', 'yearly'] as $periodicity) {
                foreach ($rates[$periodicity] ?? [] as $date => $axesRanks) {
                    $scoreProjection = $this->computeScoreProjection($axesRanks);
                    if (!empty($scoreProjection)) {
                        $scores[$periodicity][$date] = $scoreProjection;
                    }
                }
            }
            $scores['average_ranks'] = $this->computeAverageRanks($rates['average_ranks'] ?? []);
            $scores['average_ranks_consolidated_at'] = $rates['average_ranks_consolidated_at'] ?? '';

            $dashboardProjections[] = sprintf("('%s', '%s', '%s')", $line['type'], $line['code'], json_encode($scores));
        }

        if(0 === count($dashboardProjections))
        {
            return;
        }

        $dashboardProjections = implode(',', $dashboardProjections);

        $query = <<<SQL
INSERT INTO pim_data_quality_insights_dashboard_scores_projection (type, code, scores) VALUES $dashboardProjections;
SQL;

        $this->db->executeQuery($query);
    }

    private function computeScoreProjection(array $axesRanks): array
    {
        $scoreProjection = [];
        foreach ($this->getLocalesByChannelQuery() as $channel => $locales) {
            foreach ($locales as $locale) {
                if (!isset($axesRanks['enrichment'][$channel][$locale])) {
                    continue;
                }
                foreach (['rank_1', 'rank_2', 'rank_3', 'rank_4', 'rank_5' ] as $rank) {
                    $scoreProjection[$channel][$locale][$rank] = isset($axesRanks['consistency'][$channel][$locale])
                        ? intval(round(($axesRanks['enrichment'][$channel][$locale][$rank] + $axesRanks['consistency'][$channel][$locale][$rank]) / 2))
                        : $axesRanks['enrichment'][$channel][$locale][$rank];
                }
            }
        }

        return $scoreProjection;
    }

    private function computeAverageRanks(array $averageRanksByAxes): array
    {
        $averageRanks = [];
        foreach ($this->getLocalesByChannelQuery() as $channel => $locales) {
            foreach ($locales as $locale) {
                if (!isset($averageRanksByAxes['enrichment'][$channel][$locale])) {
                    continue;
                }

                $averageRanks[$channel][$locale] = isset($averageRanksByAxes['consistency'][$channel][$locale])
                    ? $this->computeAverageStringRanks($averageRanksByAxes['enrichment'][$channel][$locale], $averageRanksByAxes['consistency'][$channel][$locale])
                    : $averageRanksByAxes['enrichment'][$channel][$locale];
            }
        }

        return $averageRanks;
    }

    private function computeAverageStringRanks(string $rank1, string $rank2): string
    {
        $rank1 = intval(str_replace('rank_', '', $rank1));
        $rank2 = intval(str_replace('rank_', '', $rank2));

        $average = intval(round(($rank1 + $rank2) / 2));

        return 'rank_' . $average;
    }

    private function getLinesToMigrate(int $bulkSize): \Iterator
    {
        $query = <<<SQL
SELECT type, code, rates FROM pim_data_quality_insights_dashboard_rates_projection
SQL;
        $lines = [];
        $stmt = $this->db->executeQuery($query);

        while ($line = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $lines[] = $line;
            if (count($lines) >= $bulkSize) {
                yield $lines;
                $lines = [];
            }
        }

        if (!empty($lines)) {
            yield $lines;
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function getLocalesByChannelQuery(): array
    {
        if (null !== $this->cachedChannelLocaleArray) {
            return $this->cachedChannelLocaleArray;
        }

        $query = <<<SQL
SELECT channel.code AS channelCode, locale.code AS localeCode
FROM pim_catalog_channel_locale
INNER JOIN pim_catalog_channel channel on pim_catalog_channel_locale.channel_id = channel.id
INNER JOIN pim_catalog_locale locale on pim_catalog_channel_locale.locale_id = locale.id
ORDER BY channelCode, localeCode;
SQL;

        $statement = $this->db->executeQuery($query);

        $channelsLocales = [];
        foreach ($statement->fetchAll() as $channelLocale) {
            $channelsLocales[$channelLocale['channelCode']][] = $channelLocale['localeCode'];
        }

        $this->cachedChannelLocaleArray = $channelsLocales;

        return $channelsLocales;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
