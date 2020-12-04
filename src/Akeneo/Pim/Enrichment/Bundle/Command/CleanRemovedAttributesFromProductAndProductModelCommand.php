<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesRemover\CleanValuesOfRemovedAttributesInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
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

    private EntityManagerClearerInterface $entityManagerClearer;
    private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory;
    private string $kernelRootDir;
    private int $productBatchSize;
    private ?CleanValuesOfRemovedAttributesInterface $cleanValuesOfRemovedAttributes;

    public function __construct(
        EntityManagerClearerInterface $entityManagerClearer,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        string $kernelRootDir,
        int $productBatchSize,
        CleanValuesOfRemovedAttributesInterface $cleanValuesOfRemovedAttributes
    ) {
        parent::__construct();

        $this->entityManagerClearer = $entityManagerClearer;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->kernelRootDir = $kernelRootDir;
        $this->productBatchSize = $productBatchSize;
        $this->cleanValuesOfRemovedAttributes = $cleanValuesOfRemovedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removes all values of deleted attributes on all products and product models')
            ->addArgument('attributes', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $attributesCodes = $input->getArgument('attributes');

        if (!empty($attributesCodes)) {
            $this->cleanValues($attributesCodes, $input, $output);
            return 0;
        }

        $io = new SymfonyStyle($input, $output);
        $env = $input->getOption('env');

        $io->title('Clean removed attributes values');
        $answer = $io->confirm(
            'This command with removes all values of deleted attributes on all products and product models' . "\n" .
            'Do you want to proceed?', true);

        if (!$answer) {
            $io->text('That\'s ok, see you!');

            return 0;
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

        return 0;
    }

    private function getProducts(ProductQueryBuilderFactoryInterface $pqbFactory): CursorInterface
    {
        $pqb = $pqbFactory->create();

        return $pqb->execute();
    }

    /**
     * Iterate over given products to launch clean commands
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
     */
    private function launchCleanTask(array $productIds, string $env, string $rootDir)
    {
        $process = new Process([sprintf('%s/../bin/console', $rootDir), 'pim:product:refresh', sprintf('--env=%s', $env), implode(',', $productIds)]);
        $process->setTimeout(null);
        $process->run();
    }

    private function cleanValues(
        array $attributesCodes,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $this->cleanValuesOfRemovedAttributes->validateRemovedAttributesCodes($attributesCodes);

        $countProducts = $this->cleanValuesOfRemovedAttributes->countProductsWithRemovedAttribute($attributesCodes);
        $countProductModels = $this->cleanValuesOfRemovedAttributes->countProductModelsWithRemovedAttribute($attributesCodes);
        $countProductVariants = $this->cleanValuesOfRemovedAttributes->countProductsAndProductModelsWithInheritedRemovedAttribute($attributesCodes);

        if (0 === $countProducts + $countProductModels) {
            $output->writeln('There is no product with those attributes.');
            return;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('Clean removed attributes values');

        $confirmMessage = sprintf(
            "This command will remove the values of the attributes: \n ".
            "%s".
            "This will update: \n".
            " - %d product model(s) (and %d product variant(s)) \n".
            " - %d product(s) \n".
            "Do you want to proceed?",
            implode(array_map(function (string $attributeCode) {
                return sprintf(" - %s \n ", $attributeCode);
            }, $attributesCodes)),
            $countProductModels,
            $countProductVariants,
            $countProducts
        );

        $answer = $io->confirm($confirmMessage, true);

        if (!$answer) {
            return;
        }

        $progressBar = new ProgressBar($output, $countProducts + $countProductModels);
        $progressBar->start();

        $updateProgressBar = function (int $count) use ($progressBar) {
            $progressBar->advance($count);
        };

        $this->cleanValuesOfRemovedAttributes->cleanProductModelsWithRemovedAttribute($attributesCodes, $updateProgressBar);
        sleep(1);
        $this->cleanValuesOfRemovedAttributes->cleanProductsWithRemovedAttribute($attributesCodes, $updateProgressBar);

        $progressBar->finish();
        $io->newLine();
    }
}
