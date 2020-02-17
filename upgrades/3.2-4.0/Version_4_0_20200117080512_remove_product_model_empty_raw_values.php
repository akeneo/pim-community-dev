<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration will delete the empty values from raw values of product models.
 * For example, the value {attr: {<all_channels>: {<all_locales>: []}}} will be removed from the raw_values field.
 */
final class Version_4_0_20200117080512_remove_product_model_empty_raw_values
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
        $this->addSql('SELECT "disable migration warning"');

        $productModelsToProcess = true;
        $productModelCodesToIndex = [];
        $lastProductModelCode = null;
        while ($productModelsToProcess) {
            $productModelsToProcess = false;
            $sql = sprintf(
                "SELECT code, raw_values FROM pim_catalog_product_model %s ORDER BY code LIMIT %d",
                $lastProductModelCode !== null ? sprintf('WHERE code > "%s"', $lastProductModelCode) : '',
                self::BATCH_SIZE
            );
            $rows = $this->connection->executeQuery($sql)->fetchAll();

            foreach ($rows as $row) {
                $productModelsToProcess = true;
                $rawValues = json_decode($row['raw_values'], true);
                $cleanRawValues = $this->getValueCleaner()->cleanAllValues(['ID' => $rawValues]);
                $cleanRawValues = isset($cleanRawValues['ID']) ? $cleanRawValues['ID'] : (object) [];
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
                        $this->getProductModelDescendantsIndexer()->indexfromProductModelCodes($productModelCodesToIndex);
                        $productModelCodesToIndex = [];
                    }
                }
                $lastProductModelCode = $row['code'];
            }
        }

        $this->getProductModelIndexer()->indexFromProductModelCodes($productModelCodesToIndex);
        $this->getProductModelDescendantsIndexer()->indexfromProductModelCodes($productModelCodesToIndex);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getValueCleaner(): EmptyValuesCleaner
    {
        return $this->container->get('akeneo.pim.enrichment.factory.empty_values_cleaner');
    }

    private function getProductModelIndexer(): ProductModelIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product_model');
    }

    private function getProductModelDescendantsIndexer(): ProductModelDescendantsAndAncestorsIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product_model_descendants_and_ancestors');
    }
}
