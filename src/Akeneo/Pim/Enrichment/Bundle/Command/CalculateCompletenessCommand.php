<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Calculate the completeness of the products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateCompletenessCommand extends ContainerAwareCommand
{
    use LockableTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:completeness:calculate')
            ->setDescription('Launch the product completeness calculation');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln(sprintf('The command "%s" is still running in another process.', self::$defaultName));

            return 0;
        }

        $output->writeln("<info>Generating missing completenesses...</info>");

        $options = [
            'filters' => [
                ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null],
                ['field' => 'family', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null]
            ]
        ];

        $container = $this->getContainer();
        $container->get('akeneo_elasticsearch.client.product')->refreshIndex();

        $pqb = $container->get('pim_catalog.query.product_query_builder_factory')->create($options);
        $products = $pqb->execute();

        $productsToSave = [];
        foreach ($products as $product) {
            $productsToSave[] = $product;

            if (count($productsToSave) === $container->getParameter('pim_job_product_batch_size')) {
                $container->get('pim_catalog.saver.product')->saveAll($productsToSave);
                $container->get('pim_connector.doctrine.cache_clearer')->clear();

                $productsToSave = [];
            }
        }

        if (!empty($productsToSave)) {
            $container->get('pim_catalog.saver.product')->saveAll($productsToSave);
        }

        $output->writeln("<info>Missing completenesses generated.</info>");
    }
}
