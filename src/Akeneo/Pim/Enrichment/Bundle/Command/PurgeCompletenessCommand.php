<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Purge the completeness of the products: rows from the table "pim_catalog_completeness" will be deleted
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeCompletenessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:completeness:purge')
            ->setDescription('Purge the product completenesses');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productBatchSize = $this->getContainer()->getParameter('pim_job_product_batch_size');

        $io = new SymfonyStyle($input, $output);

        $cacheClearer = $this->getContainer()->get('pim_connector.doctrine.cache_clearer');
        $pqbFactory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $env = $input->getOption('env');

        $io->title('Purge all product completenesses');
        $io->newLine(1);

        $products = $this->getProducts($pqbFactory);

        $progressBar = new ProgressBar($output, count($products));
        $this->cleanCompletenesses($products, $progressBar, $productBatchSize, $cacheClearer, $env, $rootDir);
        $io->newLine();
        $io->text(sprintf('%d product completenesses well purged', $products->count()));
    }

    /**
     * Get products
     *
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     *
     * @return CursorInterface
     */
    private function getProducts(ProductQueryBuilderFactoryInterface $pqbFactory): CursorInterface
    {
        $pqb = $pqbFactory->create();

        return $pqb->execute();
    }

    /**
     * Iterate over given products to launch purge commands
     *
     * @param  CursorInterface               $products
     * @param  ProgressBar                   $progressBar
     * @param  int                           $productBatchSize
     * @param  EntityManagerClearerInterface $cacheClearer
     * @param  string                        $env
     * @param  string                        $rootDir
     */
    private function cleanCompletenesses(
        CursorInterface $products,
        ProgressBar $progressBar,
        int $productBatchSize,
        EntityManagerClearerInterface $cacheClearer,
        string $env,
        string $rootDir
    ): void {
        $progressBar->start();

        $productToCleanCount = 0;
        foreach ($products as $product) {
            $productIds[] = $product->getId();
            $productToCleanCount++;
            if (0 === $productToCleanCount % $productBatchSize) {
                $this->launchPurgeTask($productIds, $env, $rootDir);
                $cacheClearer->clear();
                $productIds = [];

                $progressBar->advance($productBatchSize);
            }
        }
        if (count($productIds) > 0) {
            $this->launchPurgeTask($productIds, $env, $rootDir);
        }

        $progressBar->finish();
    }

    /**
     * Lanches the purge command on given ids
     *
     * @param array  $productIds
     * @param string $env
     * @param string $rootDir
     */
    private function launchPurgeTask(array $productIds, string $env, string $rootDir)
    {
        $process = new Process([sprintf('%s/../bin/console', $rootDir), 'pim:completeness:purge-products', sprintf('--env=%s', $env), implode(',', $productIds)]);
        $process->run();
    }
}
