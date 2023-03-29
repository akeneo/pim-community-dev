<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetAllProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetExistingProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetProductUuidsNotSynchronisedBetweenEsAndMysql;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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

    public function __construct(
        private readonly ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private readonly Client $productAndProductModelClient,
        private readonly GetProductUuidsNotSynchronisedBetweenEsAndMysql $getProductNotSynchronisedBetweenEsAndMysql,
        private readonly GetExistingProductUuids $getProductExistingAmong,
        private readonly GetAllProductUuids $getAllProduct,
    ) {
        parent::__construct();
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
                null,
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
            $chunkedProductUuids = $this->getAllProduct->byBatchesOf($batchSize);
            $productCount = 0;
        } elseif (true === $input->getOption('diff')) {
            $chunkedProductUuids = $this->getProductNotSynchronisedBetweenEsAndMysql->byBatchesOf($batchSize);
            $productCount = 0;
        } elseif (!empty($input->getArgument('identifiers'))) {
            $requestedIdentifiers = $input->getArgument('identifiers');
            $existingUuids = $this->getProductExistingAmong->among($requestedIdentifiers);
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
