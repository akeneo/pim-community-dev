<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class IndexProductCommand extends ContainerAwareCommand
{
    public const PRODUCT_INDEX_COMMAND = 'pim:product:index';
    private const BULK_SIZE = 100;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var BulkObjectDetacherInterface */
    private $bulkProductDetacher;

    /** @var BulkIndexerInterface */
    private $bulkProductIndexer;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('' . self::PRODUCT_INDEX_COMMAND . '')
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

        $this->productRepository = $this->getContainer()->get('pim_catalog.repository.product');
        $this->bulkProductIndexer = $this->getContainer()->get('pim_catalog.elasticsearch.indexer.product');
        $this->bulkProductDetacher = $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');

        $isIndexAll = $input->getOption('all');
        $productIdentifiers = $input->getArgument('identifiers');

        if ($isIndexAll) {
            $totalIndexedProducts = $this->indexAll($output);
        } elseif (0 < count($productIdentifiers)) {
            $totalIndexedProducts = $this->index($output, $productIdentifiers);
        } else {
            $output->writeln('<error>Please specify a list of product identifiers to index or use the flag --all to index all products</error>');

            return;
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
        $numberOfPage = ceil($totalElements / self::BULK_SIZE);

        $output->writeln(sprintf('<info>%s products to index</info>', $totalElements));

        for ($currentPage = 1; $currentPage <= $numberOfPage; $currentPage++) {
            $offset = self::BULK_SIZE * ($currentPage - 1);
            $output->writeln(sprintf(
                'Indexing products %d to %d',
                $offset + 1,
                ($offset + self::BULK_SIZE) < $totalElements ? ($offset + self::BULK_SIZE) : $totalElements
            ));

            $products = $this->productRepository->findAllWithOffsetAndSize($offset, self::BULK_SIZE);

            $this->bulkProductIndexer->indexAll($products);
            $this->bulkProductDetacher->detachAll($products);
        }

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
        $products = $this->productRepository->findBy(['identifiers' => $identifiers]);
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
        $productBulk = [];
        $totalProductsIndexed = 0;
        foreach ($products as $product) {
            $productBulk[] = $product;

            $i++;

            if (0 === $i % self::BULK_SIZE) {
                $this->bulkProductIndexer->indexAll($productBulk);
                $this->bulkProductDetacher->detachAll($productBulk);

                $productBulk = [];

                $totalProductsIndexed += self::BULK_SIZE;

                $output->writeln(sprintf(
                    '%d on %d products indexed',
                    $totalProductsIndexed,
                    $productsCount
                ));
            }
        }

        if (!empty($productBulk)) {
            $this->bulkProductIndexer->indexAll($productBulk);
            $this->bulkProductDetacher->detachAll($productBulk);

            $totalProductsIndexed += count($productBulk);
        }

        return $totalProductsIndexed;
    }

    /**
     * @throws \RuntimeException
     */
    private function checkIndexesExist()
    {
        $productClient = $this->getContainer()->get('akeneo_elasticsearch.client.product');
        if (!$productClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->getContainer()->getParameter('product_index_name')
                )
            );
        }

        $productAndProductModelClient = $this->getContainer()->get(
            'akeneo_elasticsearch.client.product_and_product_model'
        );
        if (!$productAndProductModelClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->getContainer()->getParameter('product_and_product_model_index_name')
                )
            );
        }
    }
}
