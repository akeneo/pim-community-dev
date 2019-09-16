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
 * TODO
 */
final class Version_4_0_20190916122239_remove_empty_raw_values extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    const BATCH_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $productsToProcess = true;
        $toReindex = [];
        $page = 0;
        while ($productsToProcess) {
            $productsToProcess = false;
            $sql = sprintf(
                "SELECT identifier, raw_values FROM pim_catalog_product LIMIT %d, %s",
                $page * self::BATCH_SIZE,
                self::BATCH_SIZE
            );
            $rows = $this->connection->executeQuery($sql)->fetchAll();

            foreach ($rows as $row) {
                $productsToProcess = true;
                $rawValues = json_decode($row['raw_values'], true);
                $cleanRawValues = $this->getValueCleaner()->cleanAllValues(['ID' => $rawValues])['ID'];
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
                    $toReindex[] = $row['identifier'];
                    if (count($toReindex) % self::BATCH_SIZE === 0) {
                        $this->getProductIndexer()->indexFromProductIdentifiers($toReindex);
                        $toReindex = [];
                    }
                }
            }

            $page++;
        }

        $this->getProductIndexer()->indexFromProductIdentifiers($toReindex);
    }

    public function down(Schema $schema) : void
    {
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
