<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Removes all values of deleted attributes on all products and product models
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanRemovedAttributesFromProductAndProductModelCommand extends ContainerAwareCommand
{
    const DEFAULT_PRODUCT_BULK_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:clean-removed-attributes')
            ->setDescription('Removes all values of deleted attributes on all products and product models');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $productBatchSize = $this->getContainer()->hasParameter('pim_job_product_batch_size') ?
            $this->getContainer()->getParameter('pim_job_product_batch_size') :
            self::DEFAULT_PRODUCT_BULK_SIZE;

        $io = new SymfonyStyle($input, $output);

        $cacheClearer = $this->getContainer()->get('pim_connector.doctrine.cache_clearer');
        $pqbFactory = $this->getContainer()->get('pim_enrich.query.product_and_product_model_query_builder_factory');
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $env = $input->getOption('env');

        $io->title('Clean removed attributes values');
        $answer = $io->confirm(
            'This command with removes all values of deleted attributes on all products and product models' . "\n" .
            'Do you want to proceed?', false);

        if (!$answer) {
            $io->text('That\'s ok, see you!');

            return;
        }

        $io->text([
            'Ok, let\'s go!',
            '(If you see warnings appearing in the console output, it\'s totally normal as ',
            'the goal of the command is to avoid those warnings in the future)'
        ]);
        $io->newLine(2);

        $products = $this->getProducts($pqbFactory);

        $progressBar = new ProgressBar($output, count($products));

        $this->cleanProducts($products, $progressBar, $productBatchSize, $cacheClearer, $env, $rootDir);
        $io->newLine();
        $io->text(sprintf('%d products well cleaned', $products->count()));
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
     * Iterate over given products to launch clean jobs
     *
     * @param  CursorInterface       $products
     * @param  ProgressBar           $progressBar
     * @param  int                   $productBatchSize
     * @param  CacheClearerInterface $cacheClearer
     * @param  string                $env
     * @param  string                $rootDir
     */
    private function cleanProducts(
        CursorInterface $products,
        ProgressBar $progressBar,
        int $productBatchSize,
        CacheClearerInterface $cacheClearer,
        string $env,
        string $rootDir
    ): void {
        $progressBar->start();

        $productToCleanCount = 0;
        foreach ($products as $product) {
            $productIds[] = IdEncoder::encode($product instanceof ProductModel ? 'product_model' : 'product', $product->getId());
            $productToCleanCount++;
            if (0 === $productToCleanCount % $productBatchSize) {
                $this->launchCleanTask($productIds, $env, $rootDir);
                $cacheClearer->clear();
                $productIds = [];

                $progressBar->advance($productBatchSize);
            }
        }
        if (count($productIds) > 0) {
            $this->launchCleanTask($productIds, $env, $rootDir);
        }

        $progressBar->finish();
    }

    /**
     * Lanches the clean command on given ids
     *
     * @param array  $productIds
     * @param string $env
     * @param string $rootDir
     */
    private function launchCleanTask(array $productIds, string $env, string $rootDir)
    {
        $process = new Process([sprintf('%s/../bin/console', $rootDir), 'pim:product:refresh', sprintf('--env=%s', $env), implode(',', $productIds)]);
        $process->run();
    }
}
