<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration will delete the empty values from raw values of product and product models.
 * For example, the value {attr: {<all_channels>: {<all_locales: []}}} will be removed from the raw_values field.
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
        $this->cleanProducts();
        $this->cleanProductModels();
    }

    private function cleanProducts()
    {
        $productsToProcess = true;
        $productIdentifiersToIndex = [];
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
                    $productIdentifiersToIndex[] = $row['identifier'];
                    if (count($productIdentifiersToIndex) % self::BATCH_SIZE === 0) {
                        $this->getProductIndexer()->indexFromProductIdentifiers($productIdentifiersToIndex);
                        $productIdentifiersToIndex = [];
                    }
                }
            }

            $page++;
        }

        $this->getProductIndexer()->indexFromProductIdentifiers($productIdentifiersToIndex);
    }

    private function cleanProductModels()
    {
        $productModelsToProcess = true;
        $productModelCodesToIndex = [];
        $page = 0;
        while ($productModelsToProcess) {
            $productModelsToProcess = false;
            $sql = sprintf(
                "SELECT code, raw_values FROM pim_catalog_product_model LIMIT %d, %s",
                $page * self::BATCH_SIZE,
                self::BATCH_SIZE
            );
            $rows = $this->connection->executeQuery($sql)->fetchAll();

            foreach ($rows as $row) {
                $productModelsToProcess = true;
                $rawValues = json_decode($row['raw_values'], true);
                $cleanRawValues = $this->getValueCleaner()->cleanAllValues(['ID' => $rawValues])['ID'];
                if ($rawValues !== $cleanRawValues) {
                    $this->connection->executeQuery(
                        'UPDATE pim_catalog_product_model SET raw_values = :rawValues WHERE code = :code',
                        [
                            'rawValues' => json_encode($cleanRawValues),
                            'code' => $row['code']
                        ], [
                            'rawValues' => Type::STRING,
                            'code' => Type::STRING
                        ]
                    );
                    $productModelCodesToIndex[] = $row['code'];
                    if (count($productModelCodesToIndex) % self::BATCH_SIZE === 0) {
                        $this->getProductModelIndexer()->indexFromProductModelCodes($productModelCodesToIndex);
                        $productModelCodesToIndex = [];
                    }
                }
            }

            $page++;
        }

        $this->getProductModelIndexer()->indexFromProductModelCodes($productModelCodesToIndex);
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

    private function getProductModelIndexer(): ProductModelIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product_model');
    }
}
