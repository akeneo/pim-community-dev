<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Command;

use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index published products into Elasticsearch
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
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

        $pageSize = $input->getOption('page-size');
        $totalElements = $publishedProductRepository->countAll();
        $numberOfPage = ceil($totalElements / $pageSize);

        $output->writeln(sprintf('<info>%s published products to index</info>', $totalElements));

        for ($currentPage = 1; $currentPage <= $numberOfPage; $currentPage++) {
            $offset = $pageSize * ($currentPage - 1);
            $output->writeln(
                sprintf(
                    'Indexing published products %d to %d',
                    $offset + 1,
                    ($offset + $pageSize) < $totalElements ? ($offset + $pageSize) : $totalElements
                )
            );

            $publishedProductIndexer->indexAll($publishedProductRepository->findAllWithOffsetAndSize($offset, $pageSize), ['index_refresh' => Refresh::disable()]);
        }

        $message = sprintf('<info>%d published products indexed</info>', $totalElements);

        $output->writeln($message);
    }
}
