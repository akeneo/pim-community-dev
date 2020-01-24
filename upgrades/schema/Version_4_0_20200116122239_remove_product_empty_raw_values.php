<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration will delete the empty values from raw values of products.
 * For example, the value {attr: {<all_channels>: {<all_locales>: []}}} will be removed from the raw_values field.
 */
final class Version_4_0_20200116122239_remove_product_empty_raw_values
    extends AbstractMigration
    implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    private const BATCH_SIZE = 1000;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $this->cleanProducts();
    }

    private function cleanProducts()
    {
        $this->addSql('SELECT "disable migration warning"');

        $productsToProcess = true;
        $productIdentifiersToIndex = [];
        $lastProductIdentifier = null;
        while ($productsToProcess) {
            $productsToProcess = false;
            $sql = sprintf(
                "SELECT identifier, raw_values FROM pim_catalog_product %s ORDER BY identifier LIMIT %d",
                $lastProductIdentifier !== null ? sprintf('WHERE identifier > "%s"', $lastProductIdentifier) : '',
                self::BATCH_SIZE
            );
            $rows = $this->connection->executeQuery($sql)->fetchAll();

            foreach ($rows as $row) {
                $productsToProcess = true;
                $rawValues = json_decode($row['raw_values'], true);
                $cleanRawValues = $this->getValueCleaner()->cleanAllValues(['ID' => $rawValues]);
                $cleanRawValues = isset($cleanRawValues['ID']) ? $cleanRawValues['ID'] : (object) [];
                if ($rawValues !== $cleanRawValues) {
                    $this->connection->executeQuery(
                        'UPDATE pim_catalog_product SET raw_values = :rawValues WHERE identifier = :identifier',
                        [
                            'rawValues' => json_encode($cleanRawValues),
                            'identifier' => $row['identifier']
                        ], [
                            'rawValues' => Type::STRING,
                            'identifier' => Type::STRING
                        ]
                    );
                    $productIdentifiersToIndex[] = $row['identifier'];
                    if (count($productIdentifiersToIndex) % self::BATCH_SIZE === 0) {
                        $this->getProductIndexer()->indexFromProductIdentifiers($productIdentifiersToIndex);
                        $productIdentifiersToIndex = [];
                    }
                }
                $lastProductIdentifier = $row['identifier'];
            }
        }

        $this->getProductIndexer()->indexFromProductIdentifiers($productIdentifiersToIndex);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getValueCleaner(): EmptyValuesCleaner
    {
        return $this->container->get('akeneo.pim.enrichment.factory.empty_values_cleaner');
    }

    private function getProductIndexer(): ProductIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product');
    }
}
