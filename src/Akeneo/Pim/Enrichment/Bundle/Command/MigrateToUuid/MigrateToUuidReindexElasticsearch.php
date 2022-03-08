<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
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

    public function getMissingCount(): int
    {
        return $this->getEsResult()['hits']['total']['value'];
    }

    public function getName(): string
    {
        return 'reindex_elasticsearch';
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        if (!$this->columnExists('pim_catalog_product', 'uuid')) {
            return false;
        }

        $productIdentifiers = $this->getProductIdentifiersToIndex();
        $processedItems = 0;
        while (count($productIdentifiers) > 0) {
            $logContext->addContext('substep', 'reindex_product_uuid_batch');
            if (!$context->dryRun()) {
                $this->productIndexer->indexFromProductIdentifiers($productIdentifiers, [
                    'index_refresh' => Refresh::enable()
                ]);
            }
            $this->logger->notice(
                'Substep done',
                $logContext->toArray(['reindexed_uuids_counter' => $processedItems += count($productIdentifiers)])
            );
            $productIdentifiers = $this->getProductIdentifiersToIndex();
            if ($context->dryRun()) {
                $productIdentifiers = [];
            }
        }

        return true;
    }

    public function shouldBeExecuted(): bool
    {
        return $this->getMissingCount() > 0;
    }

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
            'fields' => ['identifier'],
            '_source' => false,
            'size' => self::BATCH_SIZE,
        ]);
    }

    private function getProductIdentifiersToIndex(): array
    {
        return array_map(
            fn (array $document): string => $document['fields']['identifier'][0],
            $this->getEsResult()['hits']['hits']
        );
    }
}
