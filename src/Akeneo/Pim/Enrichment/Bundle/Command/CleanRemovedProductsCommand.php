<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Erases documents present in Elasticsearch but not present in MySQL
 *
 * @author    Anne-Laure Jouhanneau <anne-laure.jouhanneau@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanRemovedProductsCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 1000;

    public function __construct(
        private ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private Client $productAndProductModelClient,
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'batch-size',
                false,
                InputOption::VALUE_REQUIRED,
                'Number of products to index per batch',
                self::DEFAULT_BATCH_SIZE
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkIndexExists();

        $batchSize = (int) $input->getOption('batch-size') ?: self::DEFAULT_BATCH_SIZE;

        $numberOfIndexedProducts = $this->removeDocumentFromIndex($output, $batchSize) ?? 0;

        $output->writeln('');
        $output->writeln(sprintf('<info>%d products de-indexed</info>', $numberOfIndexedProducts));

        return Command::SUCCESS;
    }

    private function removeDocumentFromIndex(OutputInterface $output, int $batchSize): int
    {
        $indexedProductCount = 0;
        $progressBar = new ProgressBar($output, 0);
        $progressBar->start();

        $chunkedNonExistentProductIds = $this->filterNonExistentProductInMySQL($batchSize);

        foreach ($chunkedNonExistentProductIds as $nonExistentProductIds) {
            $uuids = array_map(fn ($id) => Uuid::fromString(($id)), $nonExistentProductIds);
            $ancestorCodes = $this->getAncestorsFromProductsIds($nonExistentProductIds);
            $this->productAndAncestorsIndexer->removeFromProductUuidsAndReindexAncestors(
                $uuids,
                $ancestorCodes
            );
            $indexedProductCount += count($nonExistentProductIds);
            $progressBar->advance(count($nonExistentProductIds));
        }

        $progressBar->finish();

        return $indexedProductCount;
    }

    private function filterNonExistentProductInMySQL(int $batchSize): iterable
    {
        $esProductsIdsChunk = $this->fetchAllProductsIdsFromEsByChunk($batchSize);

        $sql = <<< SQL
SELECT BIN_TO_UUID(uuid) AS uuid
FROM pim_catalog_product
WHERE BIN_TO_UUID(uuid) IN (:esIdentifiers) 
SQL;

        foreach ($esProductsIdsChunk as $productIdsFromEs) {
            if (empty($productIdsFromEs)) {
                break;
            }

            $productIdsFromMysql = $this->connection->executeQuery(
                $sql,
                [
                    'esIdentifiers' => $productIdsFromEs
                ],
                [
                    'esIdentifiers' => Connection::PARAM_STR_ARRAY,
                ]
            )->fetchFirstColumn();


            $nonExistentProductIdsInMysql = array_diff($productIdsFromEs, $productIdsFromMysql);
            if (!empty($nonExistentProductIdsInMysql)) {
                yield $nonExistentProductIdsInMysql;
            }
        }
    }

    private function fetchAllProductsIdsFromEsByChunk(int $batchSize): iterable
    {
        $searchAfter = null;
        do {
            $params = array_merge(
                [
                    'sort' => ['id' => 'asc'],
                    'size' => $batchSize,
                    '_source' => ['id'],
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
                ],
                $searchAfter ? [
                    'search_after' => $searchAfter
                ] : []
            );
            $results = $this->productAndProductModelClient->search($params);
            $productsIds = array_map(function ($doc) {
                return substr($doc['id'], ElasticsearchProductProjection::INDEX_PREFIX_ID);
            }, $results['hits']['hits']);

            yield $productsIds;

            $resultsPage = $results['hits']['hits'];
            $lastResult = end($resultsPage);
            $searchAfter = $lastResult['sort'] ?? [];
        } while (count($resultsPage)>0);
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

    private function getAncestorsFromProductsIds(array $productIds): array
    {
        $prefixProductIds = array_map(fn ($productId) => ElasticsearchProductProjection::INDEX_PREFIX_ID . $productId, $productIds);
        $params =
            [
                '_source' => ['ancestors'],
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'must' => [
                                    'ids' => [
                                        'values' => $prefixProductIds
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        $results = $this->productAndProductModelClient->search($params);
        $ancestorsCodesByProducts = array_map(fn ($doc) => $doc['ancestors']['codes'], $results['hits']['hits']);

        return [...$ancestorsCodesByProducts];
    }
}
