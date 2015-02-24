<?php

namespace Pim\Bundle\EnrichBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
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
        $this
            ->setName('pim:mass-edit:change-status')
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

        $cursor = $this->getProducts($filters);
        $paginator = $this->getPaginator($cursor);
        $this->getVersionManager()->setRealTimeVersioning(false);

        $output->writeln("<info>Mass editing status on products<info>");
        foreach ($paginator as $products) {
            foreach ($products as $product) {
                $product->setEnabled((bool)$status);
            }
            $this->saveAll($products, ['recalculate' => false, 'schedule' => false]);
            // Temporary deactivated in order to find a solution that really flush UOW
            // $this->getCacheClearer()->clear();
        }
        $output->writeln("<info>Done<info>");
    }

    /**
     * @param array $products
     * @param array $options
     */
    protected function saveAll(array $products, $options = [])
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->saveAll($products, $options);
    }

    /**
     * @return \Pim\Bundle\VersioningBundle\Manager\VersionManager
     */
    protected function getVersionManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }

    /**
     * @param CursorInterface $products
     *
     * @return \Akeneo\Component\StorageUtils\Cursor\PaginatorInterface
     */
    protected function getPaginator(CursorInterface $products)
    {
        $paginatorFactory = $this->getContainer()->get('pim_enrich.product.paginator');

        return $paginatorFactory->createPaginator($products);
    }

    /**
     * @return \Pim\Bundle\TransformBundle\Cache\CacheClearer
     */
    protected function getCacheClearer()
    {
        return $this->getContainer()->get('pim_transform.cache.product_cache_clearer');
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
