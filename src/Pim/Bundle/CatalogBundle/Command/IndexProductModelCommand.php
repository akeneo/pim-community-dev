<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class IndexProductModelCommand extends ContainerAwareCommand
{
    public const PRODUCT_MODEL_INDEX_COMMAND = 'pim:product-model:index';
    private const BULK_SIZE = 100;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var BulkObjectDetacherInterface */
    private $bulkProductModelDetacher;

    /** @var BulkIndexerInterface */
    private $bulkProductModelIndexer;

    /** @var BulkIndexerInterface */
    private $bulkProductModelDescendantsIndexer;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('' . self::PRODUCT_MODEL_INDEX_COMMAND . '')
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
                'Index all existing products into Elasticsearch'
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

        $this->productModelRepository = $this->getContainer()->get('pim_catalog.repository.product_model');
        $this->bulkProductModelDetacher = $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');
        $this->bulkProductModelIndexer = $this->getContainer()->get('pim_catalog.elasticsearch.indexer.product_model');
        $this->bulkProductModelDescendantsIndexer = $this->getContainer()
            ->get('pim_catalog.elasticsearch.indexer.product_model_descendance');

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
        $numberOfPage = ceil($totalElements / self::BULK_SIZE);

        $output->writeln(sprintf('<info>%s product models to index</info>', $totalElements));

        for ($currentPage = 1; $currentPage <= $numberOfPage; $currentPage++) {
            $offset = self::BULK_SIZE * ($currentPage - 1);
            $output->writeln(sprintf(
                'Indexing product models %d to %d',
                $offset + 1,
                ($offset + self::BULK_SIZE) < $totalElements ? ($offset + self::BULK_SIZE) : $totalElements
            ));

            $rootProductModels = $this->productModelRepository->findRootProductModelsWithOffsetAndSize(
                $offset,
                self::BULK_SIZE
            );

            $this->bulkProductModelIndexer->indexAll($rootProductModels);
            $this->bulkProductModelDescendantsIndexer->indexAll($rootProductModels);
            $this->bulkProductModelDetacher->detachAll($rootProductModels);
        }

        return $totalElements;
    }

    /**
     * Indexes the given list of product model codes in Elasticsearch.
     *
     * @param OutputInterface $output
     * @param array           $codes
     *
     * @return int
     */
    private function index(OutputInterface $output, array $codes): int
    {
        $productModels = $this->productModelRepository->findBy(['code' => $codes]);
        $productModelsCount = count($productModels);

        if ($productModelsCount !== count($codes)) {
            $codesFound = [];
            foreach ($productModels as $productModel) {
                $codesFound[] = $productModel->getCode();
            }

            $notFoundCodes = array_diff($codes, $codesFound);
            $output->writeln(sprintf(
                '<error>Some product models were not found for the given codes: %s</error>',
                implode(', ', $notFoundCodes)
            ));
        }

        $output->writeln(sprintf('<info>%d product models found for indexing</info>', $productModelsCount));

        $i = 0;
        $productModelBulk = [];
        $totalProductModelsIndexed = 0;

        foreach ($productModels as $productModel) {
            $productModelBulk[] = $productModel;

            $i++;

            if (0 === $i % self::BULK_SIZE) {
                $this->bulkProductModelIndexer->indexAll($productModelBulk);
                $this->bulkProductModelDescendantsIndexer->indexAll($productModelBulk);
                $this->bulkProductModelDetacher->detachAll($productModelBulk);

                $productModelBulk = [];

                $totalProductModelsIndexed += self::BULK_SIZE;

                $output->writeln(sprintf(
                    '%d on %d product models indexed',
                    $totalProductModelsIndexed,
                    $productModelsCount
                ));
            }
        }

        if (!empty($productModelBulk)) {
            $this->bulkProductModelIndexer->indexAll($productModelBulk);
            $this->bulkProductModelDescendantsIndexer->indexAll($productModelBulk);
            $this->bulkProductModelDetacher->detachAll($productModelBulk);

            $totalProductModelsIndexed += count($productModelBulk);
        }

        return $totalProductModelsIndexed;
    }

    /**
     * @throws \RuntimeException
     */
    private function checkIndexesExist()
    {
        $productModelClient = $this->getContainer()->get('akeneo_elasticsearch.client.product_model');
        if (!$productModelClient->hasIndex()) {
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
