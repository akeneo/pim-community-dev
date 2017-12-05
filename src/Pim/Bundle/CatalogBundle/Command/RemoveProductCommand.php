<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Pim\Component\Catalog\Paginator\ProductPaginator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Query and delete products in bulk
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductCommand extends ContainerAwareCommand
{
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
            ->setName('pim:product:remove')
            ->setDescription('Query products and deletes them')
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
        $cursor = $this->getProducts($filters);
        $nbProducts = count($cursor);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            sprintf('You are going to remove %s products. Are you sure ? (Y|n)', $nbProducts),
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $paginator = $this->getPaginatorFactory()->createPaginator($cursor, 100);

        $i = 0;
        $start = microtime(true);
        foreach ($paginator as $productsPage) {
            $i++;
            $this->getProductRemover()->removeAll($productsPage, ['flush' => true]);
            $this->getProductDetacher()->detachAll($productsPage);

            echo memory_get_usage() . PHP_EOL;
            if($i % 10 === 0) {
                echo microtime(true) - $start . PHP_EOL;
                gc_collect_cycles();
                meminfo_dump(
                    fopen('/tmp/meminfo/' . 'products-' . $i . '00.json', 'w')
                );
                $start = microtime(true);
            }
        }
        echo microtime(true) - $start . PHP_EOL;
        echo memory_get_usage() . PHP_EOL;

        $output->write("Done.");
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

    /**
     * @return BulkRemoverInterface
     */
    protected function getProductRemover()
    {
        return $this->getContainer()->get('pim_catalog.remover.product');
    }

    /**
     * @return PaginatorFactoryInterface
     */
    protected function getPaginatorFactory()
    {
        return $this->getContainer()->get('pim_enrich.product.paginator');
    }

    /**
     * @return BulkObjectDetacherInterface
     */
    protected function getProductDetacher()
    {
        return $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');
    }
}
