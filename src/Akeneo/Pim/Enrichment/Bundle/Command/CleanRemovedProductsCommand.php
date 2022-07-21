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

        $chunkedProductIdentifiersAncestorsCodes = $this->filterNonExistingProductInMySQL($batchSize);

        while($chunkedProductIdentifiersAncestorsCodes->valid()){
            $arrayProductIdentifierAncestorsCodes = $chunkedProductIdentifiersAncestorsCodes->current();
            $this->productAndAncestorsIndexer->removeFromProductUuidsAndReindexAncestors(
                [Uuid::fromString($arrayProductIdentifierAncestorsCodes['_id'])],
                $arrayProductIdentifierAncestorsCodes['ancestor_codes'] ?? []
            );
            $indexedProductCount += count($arrayProductIdentifierAncestorsCodes);
            $progressBar->advance(count($arrayProductIdentifierAncestorsCodes));
        }

        $progressBar->finish();

        return $indexedProductCount;
    }

    private function filterNonExistingProductInMySQL(int $batchSize): \Generator
    {
        $esDocuments = $this->fetchAllIdentifiersFromEs($batchSize);

        $sql = <<< SQL
SELECT BIN_TO_UUID(uuid) AS uuid, identifier
FROM pim_catalog_product
WHERE BIN_TO_UUID(uuid) IN (:esIdentifiers) 
SQL;

        foreach($esDocuments as $chunkProducts) {
            while (count($chunkProducts) > 0) {
                $chunkProductIdentifiers = array_map(fn($doc): string => substr($doc['_id'], 8), $chunkProducts);

                $rows = $this->connection->executeQuery(
                    $sql,
                    [
                        'esIdentifiers' => $chunkProductIdentifiers
                    ],
                    [
                        'esIdentifiers' => Connection::PARAM_STR_ARRAY,
                    ]
                )->fetchAllAssociative();

                $mysqlIds = array_map(function ($item) {
                    return $item['uuid'];
                }, $rows);

                $chunkRemovedProductIdentifiers = array_reduce(
                    $chunkProducts,
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

                yield $chunkRemovedProductIdentifiers;
            }
        }
    }

    private function fetchAllIdentifiersFromEs(int $batchSize): iterable
    {
        $searchAfter = null;
        do {
            $params = array_merge(
                [
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
                ],
                $searchAfter ? [
                    'search_after' => $searchAfter
                ] : []
            );
            $results = $this->productAndProductModelClient->search($params);
            $esDocuments = $results['hits']['hits'];

            yield $esDocuments;

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
}
