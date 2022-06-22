<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanRemovedProductsCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 1000;

    protected static $defaultName = 'pim:product:clean-removed-products';

    public function __construct(
        private ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private Client $productAndProductModelClient,
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Erase documents present in Elasticsearch but not present in MySQL');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkIndexExists();

        $batchSize = (int) $input->getOption('batch-size') ?: self::DEFAULT_BATCH_SIZE;

        $deleteProductIdentifiers = $this->getEraseDiffElasticsearchProductIdentifiers($batchSize);

        $numberOfIndexedProducts = 0;
        if ($deleteProductIdentifiers->valid()) {
            $numberOfIndexedProducts = $this->doDeindex($deleteProductIdentifiers, new ProgressBar($output, 0));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>%d products de-indexed</info>', $numberOfIndexedProducts));

        return 0;
    }

    private function getEraseDiffElasticsearchProductIdentifiers(int $batchSize): \Generator
    {
        $sql = <<< SQL
SELECT CONCAT('product_', BIN_TO_UUID(uuid)) AS _id, BIN_TO_UUID(uuid) AS uuid, identifier
FROM pim_catalog_product
WHERE CONCAT('product_', BIN_TO_UUID(uuid)) IN (:esIdentifiers) 
SQL;
        $searchAfter = null;
        do {
            $params = array_merge([
                'sort' => ['id' => 'asc'],
                'size' => $batchSize,
                '_source' => ['id', 'identifier','ancestors'],
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    'term' => [
                                        'document_type' => ProductInterface::class
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], $searchAfter ? [
                'search_after' => $searchAfter
            ] : []);
            $results = $this->productAndProductModelClient->search($params);

            $esIdentifiers = array_map(function ($doc) {
                return $doc['_id'];
            }, $results['hits']['hits']);

            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'esIdentifiers' => $esIdentifiers
                ],
                [
                    'esIdentifiers' => Connection::PARAM_STR_ARRAY,
                ]
            )->fetchAllAssociative();

            $esDocuments = $results['hits']['hits'];
            $mysqlIds = array_map(function ($item) {
                return $item['_id'];
            }, $rows);
            $diff = array_reduce(
                $esDocuments,
                function ($carry, $item) use ($mysqlIds): array {
                    if (!in_array($item['_id'], $mysqlIds)) {
                        $carry[] = [
                            '_id' => substr(strstr($item['_id'], '_'), 1),
                            'ancestor_codes' => $item['_source']['ancestors']['codes'] ?? [],
                        ];
                    }

                    return $carry;
                },
                []
            );

            yield from $diff;

            $resultsPage = $results['hits']['hits'];
            $lastResult = end($resultsPage);
            $searchAfter = $lastResult['sort'] ?? [];
        } while (count($resultsPage)>0);
    }

    private function doDeindex(iterable $chunkedProductIdentifiersAncestorsCodes, ProgressBar $progressBar): int
    {
        $indexedProductCount = 0;
        $progressBar->start();

        foreach ($chunkedProductIdentifiersAncestorsCodes as $arrayProductIdentifierAncestorsCodes) {
            if ($arrayProductIdentifierAncestorsCodes !== null) {
                $this->productAndAncestorsIndexer->removeFromProductIdsAndReindexAncestors(
                    [$arrayProductIdentifierAncestorsCodes['_id']],
                    [Uuid::fromString($arrayProductIdentifierAncestorsCodes['_id'])],
                    $arrayProductIdentifierAncestorsCodes['ancestor_codes'] ?? []
                );
                $indexedProductCount += count($arrayProductIdentifierAncestorsCodes);
                $progressBar->advance(count($arrayProductIdentifierAncestorsCodes));
            }
        }
        $progressBar->finish();

        return $indexedProductCount;
    }

    private function checkIndexExists(): void
    {
        if (!$this->productAndProductModelClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->productAndProductModelClient->getIndexName()
                )
            );
        }
    }
}
