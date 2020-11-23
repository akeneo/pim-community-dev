<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20201118133700_migrate_product_axis_rate_to_unique_score extends AbstractMigration implements ContainerAwareInterface
{
    const BULK_SIZE = 200;

    private ?ContainerInterface $container;

    private ?Connection $db;

    private ?array $cachedChannelLocaleArray;

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $this->db = $this->container->get('database_connection');
        $this->cachedChannelLocaleArray = null;

        $lastProduct = 0;
        while ($productsToMigrate = $this->getProductsToMigrateFrom($lastProduct, self::BULK_SIZE)) {
            $this->migrateMySQLProducts($productsToMigrate);
            $lastProduct = end($productsToMigrate);
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getProductsToMigrateFrom($productId, int $limit): array
    {
        $query = <<<SQL
SELECT id FROM pim_catalog_product WHERE id > :productId ORDER BY id LIMIT :limit;
SQL;
        $products = $this->db->executeQuery(
            $query,
            ['productId' => $productId, 'limit' => $limit ],
            ['productId' => \PDO::PARAM_INT, 'limit' => \PDO::PARAM_INT ]

        )->fetchAll(\PDO::FETCH_COLUMN);

        return $products ?? [];
    }

    private function migrateMySQLProducts(array $productsToMigrate): void
    {
        if (empty($productsToMigrate)) {
            return;
        }

        $query = <<<SQL
SELECT product_id, MAX(evaluated_at) AS evaluated_at, JSON_OBJECTAGG(axis_code, rates) AS rates
FROM (
    SELECT latest_eval.axis_code, latest_eval.product_id, latest_eval.evaluated_at, latest_eval.rates
    FROM pim_data_quality_insights_product_axis_rates AS latest_eval
        LEFT JOIN pim_data_quality_insights_product_axis_rates AS other_eval
            ON other_eval.axis_code = latest_eval.axis_code
            AND other_eval.product_id = latest_eval.product_id
            AND latest_eval.evaluated_at < other_eval.evaluated_at
    WHERE latest_eval.product_id IN (:products)
        AND other_eval.evaluated_at IS NULL
) latest_product_rates
GROUP BY product_id;
SQL;

        $stmt = $this->db->executeQuery($query, ['products' => $productsToMigrate], ['products' => Connection::PARAM_INT_ARRAY]);

        $productsUniqueScores = [];
        while ($productRates = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rates = json_decode($productRates['rates'], true);
            $uniqueScores = $this->computeUniqueScores($rates);
            $productsUniqueScores[] = sprintf("(%d, '%s', '%s')", $productRates['product_id'], $productRates['evaluated_at'], json_encode($uniqueScores));
        }

        if(0 === count($productsUniqueScores))
        {
            return;
        }

        $productsUniqueScores = implode(',', $productsUniqueScores);

        $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores)
VALUES $productsUniqueScores;
SQL;

        $this->db->executeQuery($query);
    }

    private function computeUniqueScores(array $rates): array
    {
        $uniqueScores = [];
        foreach ($this->getLocalesByChannelQuery() as $channel => $locales) {
            foreach ($locales as $locale) {
                $enrichmentRate = $rates['enrichment'][$channel][$locale] ?? null;
                $consistency = $rates['consistency'][$channel][$locale] ?? null;

                if ($enrichmentRate !== null && $consistency !== null) {
                    $uniqueScores[$channel][$locale]['rank'] = intval(round(($enrichmentRate['rank'] + $consistency['rank']) / 2));
                    $uniqueScores[$channel][$locale]['value'] = intval(round(($enrichmentRate['value'] + $consistency['value']) / 2));
                } elseif ($enrichmentRate !== null && $consistency === null) {
                    $uniqueScores[$channel][$locale] = $enrichmentRate;
                } elseif ($enrichmentRate === null && $consistency !== null) {
                    $uniqueScores[$channel][$locale] = $consistency;
                }
            }
        }

        return $uniqueScores;
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
