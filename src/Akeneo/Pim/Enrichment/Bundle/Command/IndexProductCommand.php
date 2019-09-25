<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
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
 * Index products into Elasticsearch
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductCommand extends Command
{
    protected static $defaultName = 'pim:product:index';

    private const BULK_SIZE = 100;
    private const ERROR_CODE_USAGE = 1;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductIndexerInterface */
    private $productIndexer;

    /** @var ObjectManager */
    private $objectManager;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var string */
    private $productAndProductModelIndexName;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductIndexerInterface $productIndexer,
        ObjectManager $objectManager,
        Client $productAndProductModelClient,
        string $productAndProductModelIndexName
    ) {
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->productIndexer = $productIndexer;
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
                'identifiers',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of product identifiers to index',
                []
            )
            ->addOption(
                'all',
                true,
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
        $this->checkIndexesExist();

        $isIndexAll = $input->getOption('all');
        $productIdentifiers = $input->getArgument('identifiers');

        if ($isIndexAll) {
            $totalIndexedProducts = $this->indexAll($output);
        } elseif (0 < count($productIdentifiers)) {
            $totalIndexedProducts = $this->index($output, $productIdentifiers);
        } else {
            $output->writeln('<error>Please specify a list of product identifiers to index or use the flag --all to index all products</error>');

            return self::ERROR_CODE_USAGE;
        }

        $message = sprintf('<info>%d products indexed</info>', $totalIndexedProducts);

        $output->writeln($message);
    }

    /**
     * Indexes all the products in elasticsearch.
     *
     * @param OutputInterface $output
     *
     * @return int
     */
    private function indexAll(OutputInterface $output): int
    {
        $totalElements = (int) $this->productRepository->countAll();

        $output->writeln(sprintf('<info>%s products to index</info>', $totalElements));

        $lastProduct = null;
        $progressBar = new ProgressBar($output, $totalElements);

        $progressBar->start();
        while (!empty($products = $this->productRepository->searchAfter($lastProduct, self::BULK_SIZE))) {
            $identifiers = array_map(function (ProductInterface $product) {
                return $product->getIdentifier();
            }, $products);
            $this->productIndexer->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::disable()]);
            $this->objectManager->clear();

            $lastProduct = end($products);
            $progressBar->advance(count($products));
        }

        $progressBar->finish();

        return $totalElements;
    }

    /**
     * Indexes the given list of product identifiers in Elasticsearch.
     *
     * @param OutputInterface $output
     * @param array           $identifiers
     *
     * @return int
     */
    private function index(OutputInterface $output, array $identifiers): int
    {
        $products = $this->productRepository->findBy(['identifier' => $identifiers]);
        $productsCount = count($products);

        if ($productsCount !== count($identifiers)) {
            $identifiersFound = [];
            foreach ($products as $product) {
                $identifiersFound[] = $product->getIdentifier();
            }

            $notFoundIdentifiers = array_diff($identifiers, $identifiersFound);
            $output->writeln(sprintf(
                '<error>Some products were not found for the given identifiers: %s</error>',
                implode(', ', $notFoundIdentifiers)
            ));
        }

        $output->writeln(sprintf('<info>%d products found for indexing</info>', $productsCount));

        $i = 0;
        $identifiers = [];
        $totalProductsIndexed = 0;
        $progressBar = new ProgressBar($output, $productsCount);

        $progressBar->start();
        foreach ($products as $product) {
            $identifiers[] = $product->getIdentifier();

            $i++;

            if (0 === $i % self::BULK_SIZE) {
                $this->productIndexer->indexFromProductIdentifiers(
                    $identifiers,
                    ['index_refresh' => Refresh::disable()]
                );

                $this->objectManager->clear();

                $progressBar->advance(count($identifiers));

                $identifiers = [];

                $totalProductsIndexed += self::BULK_SIZE;
            }
        }

        if (!empty($identifiers)) {
            $this->productIndexer->indexFromProductIdentifiers(
                $identifiers,
                ['index_refresh' => Refresh::disable()]
            );
            $this->objectManager->clear();

            $progressBar->advance(count($identifiers));

            $totalProductsIndexed += count($identifiers);
        }
        $progressBar->finish();

        return $totalProductsIndexed;
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
