<?php

namespace Pim\Bundle\EnrichBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Command for mass status products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditStatusCommand extends ContainerAwareCommand
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
            ->setName('pim:mass-edit:status')
            ->addArgument(
                'json_filters',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'status',
                InputArgument::REQUIRED,
                'Product status: 1/0'
            )
            ->setDescription('Mass edit products');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filters = json_decode($input->getArgument('json_filters'), true);
        $status  = $input->getArgument('status');

        $filters = [
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

        $products = $this->getProducts($filters);
        $output->writeln("<info>Mass editing status on products<info>");
        foreach ($products as $product) {
            if (!$product instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf(
                        'Cannot perform mass edit action "%s" on object of type "%s", '.
                        'expecting "Pim\Bundle\CatalogBundle\Model\ProductInterface"',
                        __CLASS__,
                        get_class($product)
                    )
                );
            }

            $product->setEnabled((bool)$status);
            $this->save($product);
        }
        $output->writeln("<info>Done<info>");
    }

    /**
     * @param ProductInterface $product
     */
    protected function save(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->save($product);
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
        $factory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');

        return $factory->create();
    }
}
