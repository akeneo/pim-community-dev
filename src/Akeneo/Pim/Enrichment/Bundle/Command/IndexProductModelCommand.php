<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index product models into Elasticsearch
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelCommand extends Command
{
    protected static $defaultName = 'pim:product-model:index';

    private const DEFAULT_BATCH_SIZE = 1000;

    private const ERROR_CODE_USAGE = 1;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var ProductModelDescendantsAndAncestorsIndexer */
    private $productModelDescendantAndAncestorsIndexer;

    /** @var Connection */
    private $connection;
    private BackoffElasticSearchStateHandler $batchEsStateHandler;

    public function __construct(
        Client $productAndProductModelClient,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantAndAncestorsIndexer,
        Connection $connection
    ) {
        parent::__construct();
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productModelDescendantAndAncestorsIndexer = $productModelDescendantAndAncestorsIndexer;
        $this->connection = $connection;
        $this->batchEsStateHandler = new BackoffElasticSearchStateHandler();
    }

    /**
     * @return ProductModelDescendantsAndAncestorsIndexer
     */
    public function getProductModelDescendantAndAncestorsIndexer(): ProductModelDescendantsAndAncestorsIndexer
    {
        return $this->productModelDescendantAndAncestorsIndexer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'codes',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of product model codes to index',
                []
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Index all existing product models into Elasticsearch'
            )
            ->addOption(
                'batch-size',
                false,
                InputOption::VALUE_REQUIRED,
                'Number of product models to index per batch',
                self::DEFAULT_BATCH_SIZE
            )
            ->setDescription('Index all or some product models into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkIndexExists();

        $batchSize = (int) $input->getOption('batch-size') ?: self::DEFAULT_BATCH_SIZE;

        if (true === $input->getOption('all')) {
            $chunkedProductModelCodes = $this->getAllRootProductModelCodes($batchSize);
            $productModelCount = $this->getTotalNumberOfRootProductModels();
        } elseif (!empty($input->getArgument('codes'))) {
            $requestedCodes = $input->getArgument('codes');
            $existingroductModelCodes = $this->getExistingProductModelCodes($requestedCodes);
            $nonExistingCodes = array_diff($requestedCodes, $existingroductModelCodes);
            if (!empty($nonExistingCodes)) {
                $output->writeln(
                    sprintf(
                        '<error>Some product models were not found for the given codes: %s</error>',
                        implode(', ', $nonExistingCodes)
                    )
                );
            }
            $chunkedProductModelCodes = array_chunk($existingroductModelCodes, $batchSize);
            $productModelCount = count($existingroductModelCodes);
        } else {
            $output->writeln(
                '<error>Please specify a list of product model codes to index or use the flag --all to index all product models</error>'
            );

            return self::ERROR_CODE_USAGE;
        }

        $bulkESHandler = new class($this->productModelDescendantAndAncestorsIndexer) implements BulkEsHandlerInterface {
            private ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer;

            public function __construct(ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer)
            {
                $this->productModelDescendantsAndAncestorsIndexer = $productModelDescendantsAndAncestorsIndexer;
            }
            public function bulkExecute(array $codes): int
            {
                $this->productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($codes);
                return count($codes);
            }
        };

        $numberOfIndexedProducts = $this->doIndex($chunkedProductModelCodes, new ProgressBar($output, $productModelCount), $bulkESHandler, $output);

        $output->writeln(sprintf('<info>%d product models indexed</info>', $numberOfIndexedProducts));

        return 0;
    }

    private function doIndex(iterable $chunkedCodes, ProgressBar $progressBar, BulkEsHandlerInterface $codesEsHandler, OutputInterface $output): int
    {
        $indexedCount = 0;

        $progressBar->start();
        foreach ($chunkedCodes as $codes) {
            $treatedBachSize = $this->batchEsStateHandler->bulkExecute($codes, $codesEsHandler);
            $indexedCount+=$treatedBachSize;
            $progressBar->advance($treatedBachSize);
        }
        $progressBar->finish();

        return $indexedCount;
    }

    private function getAllRootProductModelCodes(int $batchSize): iterable
    {
        $formerId = 0;
        $sql = <<< SQL
SELECT id, code
FROM pim_catalog_product_model
WHERE id > :formerId
AND parent_id IS NULL
ORDER BY id ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'formerId' => $formerId,
                    'limit' => $batchSize,
                ],
                [
                    'formerId' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAll();

            if (empty($rows)) {
                return;
            }

            $formerId = (int)end($rows)['id'];
            yield array_column($rows, 'code');
        }
    }

    private function getExistingproductModelCodes(array $productModelCodes): array
    {
        $sql = <<<SQL
SELECT code FROM pim_catalog_product_model
WHERE code IN (:codes);
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'codes' => $productModelCodes,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll(FetchMode::COLUMN, 0);
    }

    private function getTotalNumberOfRootProductModels(): int
    {
        return (int)$this->connection->executeQuery(
            'SELECT COUNT(0) FROM pim_catalog_product_model WHERE parent_id IS NULL'
        )->fetchColumn(0);
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
}
