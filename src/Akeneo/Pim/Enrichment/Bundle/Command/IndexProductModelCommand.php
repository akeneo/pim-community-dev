<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Doctrine\Common\Persistence\ObjectManager;
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

    private const BULK_SIZE = 100;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var ProductModelIndexerInterface */
    private $productModelIndexer;

    /** @var ProductModelDescendantsIndexer */
    private $bulkProductModelDescendantsIndexer;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var string */
    private $productAndProductModelIndexName;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelDescendantsIndexer $bulkProductModelDescendantsIndexer,
        ObjectManager $objectManager,
        Client $productAndProductModelClient,
        string $productAndProductModelIndexName
    ) {
        parent::__construct();
        $this->productModelRepository = $productModelRepository;
        $this->productModelIndexer = $productModelIndexer;
        $this->bulkProductModelDescendantsIndexer = $bulkProductModelDescendantsIndexer;
        $this->objectManager = $objectManager;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productAndProductModelIndexName = $productAndProductModelIndexName;
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
                true,
                InputOption::VALUE_NONE,
                'Index all existing product models into Elasticsearch'
            )
            ->setDescription('Index all or some product models into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Once the ProductModelQueryBuilder is written, we can use it instead of the productModelRepository.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkIndexesExist();

        $isIndexAll = $input->getOption('all');
        $productModelCodes = $input->getArgument('codes');

        if ($isIndexAll) {
            $totalIndexedProductModels = $this->indexAll($output);
        } elseif (0 < count($productModelCodes)) {
            $totalIndexedProductModels = $this->index($output, $productModelCodes);
        } else {
            $output->writeln('<error>Please specify a list of product model codes to index or use the flag --all to index all product models</error>');

            return;
        }

        $message = sprintf('<info>%d product models indexed</info>', $totalIndexedProductModels);

        $output->writeln($message);
    }

    /**
     * Indexes all the product models in Elasticsearch.
     *
     * @param OutputInterface $output
     *
     * @return int
     */
    private function indexAll(OutputInterface $output): int
    {
        $totalElements = $this->productModelRepository->countRootProductModels();

        $output->writeln(sprintf('<info>%s product models to index</info>', $totalElements));

        $lastRootProductModel = null;
        $progressBar = new ProgressBar($output, $totalElements);

        $progressBar->start();
        while (!empty($rootProductModels =
            $this->productModelRepository->searchRootProductModelsAfter($lastRootProductModel, self::BULK_SIZE))) {
            $productModelCodes = array_map(function (ProductModelInterface $productModel) {
                return $productModel->getCode();
            }, $rootProductModels);

            $this->productModelIndexer->indexFromProductModelCodes(
                $productModelCodes,
                ['index_refresh' => Refresh::disable()]
            );
            $this->bulkProductModelDescendantsIndexer->indexfromProductModelCodes(
                $productModelCodes,
                ['index_refresh' => Refresh::disable()]
            );
            $this->objectManager->clear();

            $lastRootProductModel = end($rootProductModels);
            $progressBar->advance(count($rootProductModels));
        }

        $progressBar->finish();

        return $totalElements;
    }

    /**
     * Indexes the given list of product model codes in Elasticsearch.
     *
     * @param OutputInterface $output
     * @param string[]        $productModelCodes
     *
     * @return int
     */
    private function index(OutputInterface $output, array $productModelCodes): int
    {
        $output->writeln(sprintf('<info>%d product models found for indexing</info>', count($productModelCodes)));

        $i = 0;
        $productModelBulk = [];
        $totalProductModelsIndexed = 0;
        $progressBar = new ProgressBar($output, count($productModelCodes));

        $progressBar->start();
        foreach ($productModelCodes as $productModelCode) {
            $productModelBulk[] = $productModelCode;

            $i++;

            if (0 === $i % self::BULK_SIZE) {
                $this->productModelIndexer->indexFromProductModelCodes(
                    $productModelBulk,
                    ['index_refresh' => Refresh::disable()]
                );
                $this->bulkProductModelDescendantsIndexer->indexfromProductModelCodes(
                    $productModelBulk,
                    ['index_refresh' => Refresh::disable()]
                );
                $this->objectManager->clear();

                $progressBar->advance(count($productModelBulk));

                $productModelBulk = [];

                $totalProductModelsIndexed += self::BULK_SIZE;
            }
        }

        if (!empty($productModelBulk)) {
            $this->productModelIndexer->indexFromProductModelCodes(
                $productModelBulk,
                ['index_refresh' => Refresh::disable()]
            );
            $this->bulkProductModelDescendantsIndexer->indexfromProductModelCodes(
                $productModelBulk,
                ['index_refresh' => Refresh::disable()]
            );
            $this->objectManager->clear();

            $progressBar->advance(count($productModelBulk));

            $totalProductModelsIndexed += count($productModelBulk);
        }
        $progressBar->finish();

        return $totalProductModelsIndexed;
    }

    /**
     * @throws \RuntimeException
     */
    private function checkIndexesExist()
    {
        if (!$this->productAndProductModelClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->productAndProductModelIndexName
                )
            );
        }
    }
}
