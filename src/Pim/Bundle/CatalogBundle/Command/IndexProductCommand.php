<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
    /** @var integer */
    const DEFAULT_PAGE_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:index')
            ->addOption(
                'page-size',
                false,
                InputOption::VALUE_OPTIONAL,
                'Number of products per page',
                self::DEFAULT_PAGE_SIZE
            )
            ->setDescription('Index all products into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productRepository = $this->getContainer()->get('pim_catalog.repository.product');
        $productIndexer = $this->getContainer()->get('pim_catalog.elasticsearch.indexer.product');

        $pageSize = $input->getOption('page-size');
        $totalElements = $productRepository->countAll();
        $numberOfPage = ceil($totalElements / $pageSize);

        $output->writeln(sprintf('<info>%s products to index</info>', $totalElements));

        for ($currentPage = 1; $currentPage <= $numberOfPage; $currentPage++) {
            $offset = $pageSize * ($currentPage - 1);
            $output->writeln(
                sprintf(
                    'Indexing products %d to %d',
                    $offset + 1,
                    ($offset + $pageSize) < $totalElements ? ($offset + $pageSize) : $totalElements
                )
            );

            $productIndexer->indexAll($productRepository->findAllWithOffsetAndSize($offset, $pageSize));
        }

        $message = sprintf('<info>%d products indexed</info>', $totalElements);

        $output->writeln($message);
    }
}
