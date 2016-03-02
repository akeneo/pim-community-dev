<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'value'    => 'Ak'
            ],
            [
                'field'    => 'completeness',
                'operator' => '=',
                'value'    => '100',
                'locale'   => 'en_US',
                'scope'    => 'print'
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
        $products = $this->getProducts($filters, $pageSize);
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
                $result[] = $product->getIdentifier()->getData();
            }

            $output->write(json_encode($result));
        }
    }

    /**
     * @param CursorInterface $products
     * @param int             $maxRows
     *
     * @return \Symfony\Component\Console\Helper\HelperInterface
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
     * @return CursorInterface
     */
    protected function getProducts(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value'])
            ->setDefined(['locale', 'scope'])
            ->setDefaults(['locale' => null, 'scope' => null]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $context = ['locale' => $filter['locale'], 'scope' => $filter['scope']];
            $productQueryBuilder->addFilter($filter['field'], $filter['operator'], $filter['value'], $context);
        }

        return $productQueryBuilder->execute();
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function getProductQueryBuilder()
    {
        $factory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');

        return $factory->create();
    }
}
