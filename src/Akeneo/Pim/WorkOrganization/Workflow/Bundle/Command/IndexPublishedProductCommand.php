<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index published products into Elasticsearch
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexPublishedProductCommand extends Command
{
    protected static $defaultName = 'pimee:published-product:index';

    /** @var integer */
    const DEFAULT_PAGE_SIZE = 100;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var BulkIndexerInterface */
    private $bulkIndexer;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        BulkIndexerInterface $bulkIndexer,
        EntityManagerClearerInterface $entityManagerClearer
    ) {
        parent::__construct();

        $this->productRepository = $productRepository;
        $this->bulkIndexer = $bulkIndexer;
        $this->entityManagerClearer = $entityManagerClearer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'page-size',
                false,
                InputOption::VALUE_OPTIONAL,
                'Number of products per page',
                self::DEFAULT_PAGE_SIZE
            )
            ->setDescription('Index all published products into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bulkSize = $input->getOption('page-size') ?? self::DEFAULT_PAGE_SIZE;

        $totalElements = $this->productRepository->countAll();

        $output->writeln(sprintf('<info>%s published products to index</info>', $totalElements));

        $lastProduct = null;
        $progress = 0;

        while (!empty($publishedProducts = $this->productRepository->searchAfter($lastProduct, $bulkSize))) {
            $output->writeln(sprintf(
                'Indexing published products %d to %d',
                $progress + 1,
                $progress + count($publishedProducts)
            ));

            $this->bulkIndexer->indexAll($publishedProducts, ['index_refresh' => Refresh::disable()]);
            $this->entityManagerClearer->clear();

            $lastProduct = end($publishedProducts);
            $progress += count($publishedProducts);
        }

        $message = sprintf('<info>%d published products indexed</info>', $totalElements);

        $output->writeln($message);

        return 0;
    }
}
