<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command iterate over the given products and purge their completeness
 *
 * @author    Julien Sanchez <jjanvier@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeProductsCompletenessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:completeness:purge-products')
            ->addArgument(
                'identifiers',
                InputArgument::REQUIRED,
                'The product identifiers to purge (comma separated values)'
            )
            ->setHidden(true)
            ->setDescription('Purge the completenesses of the given products');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $completenessRemover = $this->getContainer()
            ->get('pim_catalog.remover.completeness');
        $pqbFactory = $this->getContainer()
            ->get('pim_catalog.query.product_query_builder_factory');
        $identifiers = $input->getArgument('identifiers');

        $pqb = $pqbFactory->create();
        $pqb->addFilter('id', Operators::IN_LIST, explode(',', $identifiers));
        $products = $pqb->execute();

        foreach ($products as $product) {
            $completenessRemover->removeForProduct($product);
        }
    }
}
