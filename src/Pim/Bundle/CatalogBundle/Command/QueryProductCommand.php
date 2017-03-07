<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Query products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryProductCommand extends ContainerAwareCommand
{
    /* @var integer */
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $filtersExample = [
            [
                'field'    => 'sku',
                'operator' => 'STARTS WITH',
                'value'    => 'Ak',
            ],
            [
                'field'    => 'completeness',
                'operator' => '=',
                'value'    => '100',
                'context'  => [
                    'locale' => 'en_US',
                    'scope'  => 'print',
                ],
            ]
        ];

        $this
            ->setName('pim:product:query')
            ->setDescription('Query products')
            ->addArgument(
                'json_filters',
                InputArgument::REQUIRED,
                sprintf("The product filters in json, for instance, '%s'", json_encode($filtersExample))
            )
            ->addOption(
                'json-output',
                false,
                InputOption::VALUE_NONE,
                'If defined, output the result in json format'
            )
            ->addOption(
                'page-size',
                false,
                InputOption::VALUE_OPTIONAL,
                'If defined, display this page',
                self::DEFAULT_PAGE_SIZE
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filters = json_decode($input->getArgument('json_filters'), true);

        $pageSize = $input->getOption('page-size');
        $productQueryBuilder = $this->getProductQueryBuilder($filters);
        $products = $productQueryBuilder->execute();

        if (!$input->getOption('json-output')) {
            $table = $this->buildTable($products, $pageSize);
            $table->render($output);

            $nbProducts = count($products);
            if ($nbProducts > $pageSize) {
                $output->writeln(
                    sprintf(
                        '<info>%d first products on %d matching these criterias</info>',
                        $pageSize,
                        $nbProducts
                    )
                );
            } else {
                $output->writeln(
                    sprintf(
                        '<info>%d products are matching these criterias</info>',
                        $nbProducts
                    )
                );
            }
        } else {
            $result = [];
            foreach ($products as $product) {
                $result[] = $product->getIdentifier();
            }

            $output->write(json_encode($result));
        }
    }

    /**
     * @param CursorInterface $products
     * @param int             $maxRows
     *
     * @return HelperInterface
     */
    protected function buildTable(CursorInterface $products, $maxRows)
    {
        $helperSet = $this->getHelperSet();
        $rows = [];
        $ind = 0;
        foreach ($products as $product) {
            if ($ind++ < $maxRows) {
                $rows[] = [$product->getId(), $product->getIdentifier()];
            } else {
                $rows[] = ['...', '...'];
                break;
            }
        }
        $headers = ['id', 'identifier'];
        $table = $helperSet->get('table');
        $table->setHeaders($headers)->setRows($rows);

        return $table;
    }

    /**
     * @param array $filters
     *
     * @return ProductQueryBuilderInterface
     */
    protected function getProductQueryBuilder(array $filters)
    {
        $factory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');

        return $factory->create(['filters' => $filters]);
    }
}
