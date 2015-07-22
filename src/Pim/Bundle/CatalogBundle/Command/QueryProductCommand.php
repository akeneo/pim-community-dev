<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
        $jsonFilters = $input->getArgument('json_filters');
        $filters = json_decode($jsonFilters, true);
        if (null === $filters) {
            $output->writeln(
                sprintf('<error>You must provide valid filters</info>')
            );
        }

        $pageSize = $input->getOption('page-size');
        $productCursor = $this->getProductsCursor($filters);
        $productPaginator = $this->createProductPaginator($productCursor, $pageSize);
        $productPaginator->next();
        $productPage = $productPaginator->current();

        if (!$input->getOption('json-output')) {
            $table = $this->buildTable($productPage);
            $table->render($output);

            if ($productCursor->count() > $productPaginator->getPageSize()) {
                $output->writeln(
                    sprintf(
                        '<info>%d first products on %d matching these criterias</info>',
                        $productPaginator->getPageSize(),
                        $productCursor->count()
                    )
                );
            }
        } else {
            $result = [];
            foreach ($productPage as $product) {
                $result[] = $product->getIdentifier()->getData();
            }

            $output->write(json_encode($result));
        }
    }

    /**
     * @param ProductInterface[] $products
     *
     * @return \Symfony\Component\Console\Helper\HelperInterface
     */
    protected function buildTable(array $products)
    {
        $helperSet = $this->getHelperSet();
        $rows = [];
        foreach ($products as $product) {
            $rows[] = [$product->getId(), $product->getIdentifier()];
        }
        $rows[] = ['...', '...'];

        $headers = ['id', 'identifier'];
        $table = $helperSet->get('table');
        $table->setHeaders($headers)->setRows($rows);

        return $table;
    }

    /**
     * @param array   $filters
     *
     * @return ProductInterface[]
     */
    protected function getProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->createProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);
        $resolver->setOptional(['locale', 'scope']);
        $resolver->setDefaults(['locale' => null, 'scope' => null]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $context = ['locale' => $filter['locale'], 'scope' => $filter['scope']];
            $productQueryBuilder->addFilter($filter['field'], $filter['operator'], $filter['value'], $context);
        }
        $cursor = $productQueryBuilder->execute();

        return $cursor;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function createProductQueryBuilder()
    {
        $factory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');

        return $factory->create();
    }

    /**
     * @param CursorInterface $cursor
     * @param integer         $pageSize
     *
     * @return PaginatorInterface
     */
    protected function createProductPaginator(CursorInterface $cursor, $pageSize)
    {
        $factory = $this->getContainer()->get('pim_catalog.query.paginator.paginator_factory');

        return $factory->createPaginator($cursor, $pageSize);
    }
}
