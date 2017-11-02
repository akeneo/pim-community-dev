<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class IndexPublishedProductCommand extends ContainerAwareCommand
{
    /** @var integer */
    const DEFAULT_PAGE_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pimee:published-product:index')
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
        $publishedProductRepository = $this->getContainer()->get('pimee_workflow.repository.published_product');
        $publishedProductIndexer = $this->getContainer()->get('pim_catalog.elasticsearch.published_product_indexer');

        $bulkSize = $input->getOption('page-size') ?? self::DEFAULT_PAGE_SIZE;

        $totalElements = $publishedProductRepository->countAll();

        $output->writeln(sprintf('<info>%s published products to index</info>', $totalElements));

        $lastProduct = null;
        $progress = 0;

        while (!empty($publishedProducts = $publishedProductRepository->searchAfter($lastProduct, $bulkSize))) {
            $output->writeln(sprintf(
                'Indexing published products %d to %d',
                $progress + 1,
                $progress + count($publishedProducts)
            ));

            $publishedProductIndexer->indexAll($publishedProducts, ['index_refresh' => Refresh::disable()]);

            $lastProduct = end($publishedProducts);
            $progress += count($publishedProducts);
        }

        $message = sprintf('<info>%d published products indexed</info>', $totalElements);

        $output->writeln($message);
    }
}
