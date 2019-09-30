<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
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
 * Index products into Elasticsearch
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductCommand extends Command
{
    private const BATCH_SIZE = 1000;

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
            ->setDescription('Index all or some products into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkIndexExists();

        if (true === $input->getOption('all')) {
            $chunkedProductIdentifiers = $this->getAllProductIdentifiers();
            $productCount = $this->getTotalNumberOfProducts();
        } elseif (!empty($input->getArgument('identifiers'))) {
            $requestedIdentifiers = $input->getArgument('identifiers');
            $existingIdentifiers = $this->getExistingProductIdentifiers($requestedIdentifiers);
            $nonExistingIdentifiers = array_diff($requestedIdentifiers, $existingIdentifiers);
            if (!empty($nonExistingIdentifiers)) {
                $output->writeln(
                    sprintf(
                        '<error>Some products were not found for the given identifiers: %s</error>',
                        implode(', ', $nonExistingIdentifiers)
                    )
                );
            }
            $chunkedProductIdentifiers = array_chunk($existingIdentifiers, self::BATCH_SIZE);
            $productCount = count($existingIdentifiers);
        } else {
            $output->writeln(
                '<error>Please specify a list of product identifiers to index or use the flag --all to index all products</error>'
            );

            return self::ERROR_CODE_USAGE;
        }

        $numberOfIndexedProducts = $this->doIndex($chunkedProductIdentifiers, new ProgressBar($output, $productCount));

        $output->writeln('');
        $output->writeln(sprintf('<info>%d products indexed</info>', $numberOfIndexedProducts));

        return 0;
    }

    private function doIndex(iterable $chunkedProductIdentifiers, ProgressBar $progressBar): int
    {
        $indexedProductCount = 0;

        $progressBar->start();
        foreach ($chunkedProductIdentifiers as $productIdentifiers) {
            $this->productAndAncestorsIndexer->indexFromProductIdentifiers($productIdentifiers);
            $indexedProductCount += count($productIdentifiers);
            $progressBar->advance(count($productIdentifiers));
        }
        $progressBar->finish();

        return $indexedProductCount;
    }

    private function getAllProductIdentifiers(): iterable
    {
        $formerId = 0;
        $sql = <<< SQL
SELECT id, identifier
FROM pim_catalog_product
WHERE id > :formerId
ORDER BY id ASC
LIMIT :limit
SQL;
        while (true) {
            $rows = $this->connection->executeQuery(
                $sql,
                [
                    'formerId' => $formerId,
                    'limit' => self::BATCH_SIZE,
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
            yield array_column($rows, 'identifier');
        }
    }

    private function getTotalNumberOfProducts(): int
    {
        return (int)$this->connection->executeQuery('SELECT COUNT(0) FROM pim_catalog_product')->fetchColumn(0);
    }

    private function getExistingProductIdentifiers(array $identifiers): array
    {
        $sql = <<<SQL
SELECT identifier
FROM pim_catalog_product
WHERE identifier IN (:identifiers);
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'identifiers' => $identifiers,
            ],
            [
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll(FetchMode::COLUMN, 0);
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
