<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;
use Symfony\Component\Console\Command\Command;
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
class CleanRemovedAttributesFromProductAndProductModelCommand extends Command
{
    protected static $defaultName = 'pim:product:clean-removed-attributes';

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var string */
    private $kernelRootDir;

    /** @var int */
    private $productBatchSize;

    public function __construct(
        EntityManagerClearerInterface $entityManagerClearer,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        string $kernelRootDir,
        int $productBatchSize
    ) {
        parent::__construct();

        $this->entityManagerClearer = $entityManagerClearer;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->kernelRootDir = $kernelRootDir;
        $this->productBatchSize = $productBatchSize;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of deleted attributes on all products and product models');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $env = $input->getOption('env');

        $io->title('Clean removed attributes values');
        $answer = $io->confirm(
            'This command with removes all values of deleted attributes on all products and product models' . "\n" .
            'Do you want to proceed?', true);

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

        $products = $this->getProducts($this->productQueryBuilderFactory);

        $progressBar = new ProgressBar($output, count($products));

        $this->cleanProducts($products, $progressBar, $this->productBatchSize, $this->entityManagerClearer, $env, $this->kernelRootDir);
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
     * Iterate over given products to launch clean commands
     *
     * @param  CursorInterface               $products
     * @param  ProgressBar                   $progressBar
     * @param  int                           $productBatchSize
     * @param  EntityManagerClearerInterface $cacheClearer
     * @param  string                        $env
     * @param  string                        $rootDir
     */
    private function cleanProducts(
        CursorInterface $products,
        ProgressBar $progressBar,
        int $productBatchSize,
        EntityManagerClearerInterface $cacheClearer,
        string $env,
        string $rootDir
    ): void {
        $progressBar->start();
        $productIds = [];

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
        $process->setTimeout(null);
        $process->run();
    }
}
