<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Builder\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $filtersExample = [
            [
                'field' => 'sku',
                'operator' => 'STARTS WITH',
                'value' => 'Ak'
            ],
            [
                'field' => 'completeness',
                'operator' => '=',
                'value' => '100',
                'locale' => 'en_US',
                'scope' => 'print'
            ]
        ];

        $this
            ->setName('pim:product:query')
            ->setDescription('Query products')
            ->addArgument(
                'json_filters',
                InputArgument::REQUIRED,
                sprintf("The product filters in json, for instance, '%s'", json_encode($filtersExample))
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filters = json_decode($input->getArgument('json_filters'), true);
        $products = $this->getProducts($filters);

        $maxRows = 10;
        $table = $this->buildTable($products, $maxRows);
        $table->render($output);

        $nbProducts = count($products);
        if ($nbProducts > $maxRows) {
            $output->writeln(
                sprintf('<info>%d first products on %d matching these criterias<info>', $maxRows, $nbProducts)
            );
        }
    }

    /**
     * @param array $products
     *
     * @return \Symfony\Component\Console\Helper\HelperInterface
     */
    protected function buildTable(array $products, $maxRows)
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
     * @return ProductInterface[]
     */
    protected function getProducts(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);
        $resolver->setOptional(['locale', 'scope']);
        $resolver->setDefaults(['locale' => null, 'scope' => null]);

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
        $factory = $this->getContainer()->get('pim_catalog.doctrine.query.product_query_factory');

        return $factory->create();
    }
}
