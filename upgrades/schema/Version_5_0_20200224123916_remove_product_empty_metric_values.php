<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration will delete the empty values from metric values of products.
 * For example, the value {attr: {<all_channels>: {<all_locales>: {"amount": null, "unit": null}}}} will be removed
 * from the raw_values field.
 */
final class Version_5_0_20200224123916_remove_product_empty_metric_values
    extends AbstractMigration
    implements ContainerAwareInterface
{
    private const MYSQL_BATCH_SIZE = 1000;
    private const ELASTICSEARCH_BATCH_SIZE = 100;

    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT "disable migration warning"');

        /** @var string[] $metricAttributesCodes */
        $metricAttributesCodes = $this->findMetricAttributesCodes();

        $productIdentifiersToIndex = [];

        $rows = $this->getAllProducts();
        foreach ($rows as $i => $row) {
            $values = json_decode($row['raw_values'], true);

            if (empty(array_intersect(array_keys($values), $metricAttributesCodes))) {
                continue;
            }

            $cleanValues = $this->cleanMetricValues($values, $metricAttributesCodes);

            if ($values !== $cleanValues) {
                $this->connection->executeQuery(
                    'UPDATE pim_catalog_product SET raw_values = :rawValues WHERE identifier = :identifier',
                    [
                        'rawValues' => json_encode($cleanValues, JSON_FORCE_OBJECT),
                        'identifier' => $row['identifier'],
                    ], [
                        'rawValues' => Types::STRING,
                        'identifier' => Types::STRING,
                    ]
                );

                $productIdentifiersToIndex[] = $row['identifier'];
                if (count($productIdentifiersToIndex) % self::ELASTICSEARCH_BATCH_SIZE === 0) {
                    $this->getProductIndexer()->indexFromProductIdentifiers($productIdentifiersToIndex);
                    $productIdentifiersToIndex = [];
                }
            }
        }

        if (!empty($productIdentifiersToIndex)) {
            $this->getProductIndexer()->indexFromProductIdentifiers($productIdentifiersToIndex);
        }
    }

    private function cleanMetricValues(array $values, array $metricAttributesCodes): array
    {
        $results = [];

        foreach ($values as $attributeCode => $channelValues) {
            foreach ($channelValues as $channel => $localeValues) {
                foreach ($localeValues as $locale => $data) {
                    if (!in_array($attributeCode, $metricAttributesCodes)) {
                        $results[$attributeCode][$channel][$locale] = $data;
                        continue;
                    }

                    if ($this->isMetricFilled($data)) {
                        $results[$attributeCode][$channel][$locale] = $data;
                    }
                }
            }
        }

        return $results;
    }

    private function isMetricFilled($data)
    {
        if (null === $data) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        return isset($data['unit']) && isset($data['amount']);
    }

    private function findMetricAttributesCodes(): array
    {
        $sql = "SELECT code FROM pim_catalog_attribute WHERE attribute_type = 'pim_catalog_metric'";

        return $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getAllProducts(): \Generator
    {
        $lastId = null;

        while (true) {
            $sql = sprintf(
                "SELECT identifier, raw_values FROM pim_catalog_product %s ORDER BY identifier LIMIT %d",
                $lastId !== null ? sprintf('WHERE identifier > "%s"', $lastId) : '',
                self::MYSQL_BATCH_SIZE
            );

            $rows = $this->connection->executeQuery($sql)->fetchAll();

            if (count($rows) === 0) {
                return null;
            }

            foreach ($rows as $row) {
                yield $row;
                $lastId = $row['identifier'];
            }
        }
    }

    private function getProductIndexer(): ProductIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
