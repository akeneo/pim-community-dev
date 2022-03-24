<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidReindexElasticsearch implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    private const BATCH_SIZE = 500;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
        private Client $esClient,
        private ProductIndexerInterface $productIndexer
    ) {
    }

    /**
     * {@inerhitdoc}
     */
    public function getMissingCount(): int
    {
        return $this->getEsResult()['hits']['total']['value'];
    }

    /**
     * {@inerhitdoc}
     */
    public function getName(): string
    {
        return 'reindex_elasticsearch';
    }

    /**
     * {@inerhitdoc}
     */
    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return false;
        }

        $productIdentifiers = $this->getProductIdentifiersToIndex();
        $processedItems = 0;
        while (\count($productIdentifiers) > 0) {
            $logContext->addContext('substep', 'reindex_product_uuid_batch');
            if (!$context->dryRun()) {
                $existingIdentifiers = $this->deleteNonExistingIdentifiers($productIdentifiers);
                try {
                    $this->productIndexer->indexFromProductIdentifiers($existingIdentifiers, [
                        'index_refresh' => Refresh::enable()
                    ]);
                } catch (ObjectNotFoundException) {
                    // handle the case where a product was deleted right after checking for its existence in DB,
                    // and just before computing the ES projections
                    $existingIdentifiers = $this->deleteNonExistingIdentifiers($existingIdentifiers);
                    $this->productIndexer->indexFromProductIdentifiers($existingIdentifiers, [
                        'index_refresh' => Refresh::enable()
                    ]);
                }
            }
            $this->logger->notice(
                'Substep done',
                $logContext->toArray(['reindexed_uuids_counter' => $processedItems += \count($productIdentifiers)])
            );
            $productIdentifiers = $this->getProductIdentifiersToIndex();
            if ($context->dryRun()) {
                $productIdentifiers = [];
            }
        }

        return true;
    }

    /**
     * {@inerhitdoc}
     */
    public function shouldBeExecuted(): bool
    {
        return $this->getMissingCount() > 0;
    }

    /**
     * {@inerhitdoc}
     */
    public function getDescription(): string
    {
        return 'Reindex products in Elasticsearch using uuid';
    }

    private function getEsResult(): array
    {
        /**
         * The document to reindex still have the previous id in the product_123 format, with 123 as mysql id.
         * The new documents will have an id like product_1e40-4c55-a415-89c7958b270d, with their uuid.
         */
        return $this->esClient->search([
            'query' => [
                'regexp' => [
                    'id' => ['value' => 'product_[0-9]+']
                ]
            ],
            'fields' => ['id', 'identifier'],
            '_source' => false,
            'size' => self::BATCH_SIZE,
        ]);
    }

    private function getProductIdentifiersToIndex(): array
    {
        $identifiers = [];
        foreach ($this->getEsResult()['hits']['hits'] as $hit) {
            $id = \substr($hit['fields']['id'][0], 8);
            $identifiers[(int) $id] = $hit['fields']['identifier'][0];
        }

        return $identifiers;
    }

    /**
     * @param array<int, string> $productIdentifiers
     *
     * @return array<int, string>
     */
    private function deleteNonExistingIdentifiers(array $productIdentifiers)
    {
        $existingIdentifiers = $this->connection->executeQuery(
            'SELECT id, identifier FROM pim_catalog_product WHERE identifier IN (:identifiers)',
            ['identifiers' => $productIdentifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllKeyValue();

        $nonExistingIds = \array_keys(\array_diff_key($productIdentifiers, $existingIdentifiers));
        $this->productIndexer->removeFromProductIds($nonExistingIds);

        return $existingIdentifiers;
    }
}
