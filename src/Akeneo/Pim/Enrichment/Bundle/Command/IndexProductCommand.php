<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index products into Elasticsearch
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 1000;

    private const ERROR_CODE_USAGE = 1;

    protected static $defaultName = 'pim:product:index';

    /** @var ProductAndAncestorsIndexer */
    private $productAndAncestorsIndexer;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var Connection */
    private $connection;

    public function __construct(
        ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        Client $productAndProductModelClient,
        Connection $connection
    ) {
        parent::__construct();
        $this->productAndAncestorsIndexer = $productAndAncestorsIndexer;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'identifiers',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of product identifiers to index',
                []
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Index all existing products into Elasticsearch'
            )
            ->addOption(
                'diff',
                'd',
                InputOption::VALUE_NONE,
                'Resolve differences between MySQL and Elasticsearch'
            )
            ->addOption(
                'batch-size',
                false,
                InputOption::VALUE_REQUIRED,
                'Number of products to index per batch',
                self::DEFAULT_BATCH_SIZE
            )
            ->setDescription('Index all or some products into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkIndexExists();

        $batchSize = (int) $input->getOption('batch-size') ?: self::DEFAULT_BATCH_SIZE;

        if (true === $input->getOption('all')) {
            $chunkedProductUuids = $this->getAllProductUuids($batchSize);
            $productCount = 0;
        } elseif (true === $input->getOption('diff')) {
            $chunkedProductUuids = $this->getDiffProductUuids($batchSize);
            $productCount = 0;
        } elseif (!empty($input->getArgument('identifiers'))) {
            $requestedIdentifiers = $input->getArgument('identifiers');
            $existingUuids = $this->getExistingProductUuids($requestedIdentifiers);
            $nonExistingIdentifiers = array_diff($requestedIdentifiers, array_keys($existingUuids));
            if (!empty($nonExistingIdentifiers)) {
                $output->writeln(
                    sprintf(
                        '<error>Some products were not found for the given identifiers: %s</error>',
                        implode(', ', $nonExistingIdentifiers)
                    )
                );
            }
            $chunkedProductUuids = array_chunk($existingUuids, $batchSize);
            $productCount = count($existingUuids);
        } else {
            $output->writeln(
                '<error>Please specify a list of product identifiers to index or use the flag --all to index all products</error>'
            );

            return self::ERROR_CODE_USAGE;
        }

        $numberOfIndexedProducts = $this->doIndex($chunkedProductUuids, new ProgressBar($output, $productCount));

        $output->writeln('');
        $output->writeln(sprintf('<info>%d products indexed</info>', $numberOfIndexedProducts));

        return 0;
    }

    private function doIndex(iterable $chunkedProductUuids, ProgressBar $progressBar): int
    {
        $indexedProductCount = 0;

        $progressBar->start();
        foreach ($chunkedProductUuids as $productUuids) {
            $this->productAndAncestorsIndexer->indexFromProductUuids($productUuids);
            $indexedProductCount += count($productUuids);
            $progressBar->advance(count($productUuids));
        }
        $progressBar->finish();

        return $indexedProductCount;
    }

    private function getAllProductUuids(int $batchSize): iterable
    {
        $lastUuidAsBytes = '';
        $sql = <<<SQL
SELECT uuid
FROM pim_catalog_product
WHERE uuid > :lastUuid
ORDER BY uuid ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->fetchFirstColumn(
                $sql,
                [
                    'lastUuid' => $lastUuidAsBytes,
                    'limit' => $batchSize,
                ],
                [
                    'lastUuid' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            );

            if (empty($rows)) {
                return;
            }

            $lastUuidAsBytes = end($rows);

            yield array_map(fn (string $uuid): UuidInterface => Uuid::fromBytes($uuid), $rows);
        }
    }

    private function getExistingProductUuids(array $identifiers): array
    {
        $sql = <<<SQL
SELECT identifier, BIN_TO_UUID(uuid) AS uuid
FROM pim_catalog_product
WHERE identifier IN (:identifiers);
SQL;

        $uuids = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAllKeyValue();

        return array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids);
    }

    /**
     * @throws \RuntimeException
     */
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

    private function getDiffProductUuids(int $batchSize)
    {
        $lastUuidAsBytes = '';
        $sql = <<< SQL
SELECT CONCAT('product_',BIN_TO_UUID(uuid)) AS _id, uuid, DATE_FORMAT(updated, '%Y-%m-%dT%TZ') AS updated
FROM pim_catalog_product
WHERE uuid > :lastUuid
ORDER BY uuid ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'lastUuid' => $lastUuidAsBytes,
                    'limit' => $batchSize,
                ],
                [
                    'lastUuid' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAllAssociative();

            if (empty($rows)) {
                return;
            }

            $lastUuidAsBytes = end($rows)['uuid'];

            $existingMysqlIdentifiers = array_column($rows, '_id');
            $existingMysqlUpdated = array_column($rows, 'updated');

            $results = $this->productAndProductModelClient->search([
                'query' => [
                    'bool' => [
                        'must' => [
                            'ids' => [
                                'values' => $existingMysqlIdentifiers
                            ]
                        ],
                        'filter' => [
                            'terms' => [
                                'entity_updated' => $existingMysqlUpdated
                            ]
                        ]
                    ]
                ],
                '_source' => false,
                'size' => $batchSize
            ]);

            $esIdentifiers = array_map(function ($doc) {
                return $doc['_id'];
            }, $results["hits"]["hits"]);

            $diff = array_reduce(
                $rows,
                function ($carry, $item) use ($esIdentifiers) {
                    if (!in_array($item['_id'], $esIdentifiers)) {
                        $carry[] = Uuid::fromBytes($item['uuid']);
                    }

                    return $carry;
                },
                []
            );

            yield $diff;
        }
    }
}
