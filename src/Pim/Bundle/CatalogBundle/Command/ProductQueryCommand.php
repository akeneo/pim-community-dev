<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Example query product command
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:debug:product-query')
            ->setDescription('Test the product query');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = $this->getProductQueryFactory();
        $pqb = $factory->create(['locale_code' => 'en_US', 'scope_code' => 'ecommerce']);

        $pqb
            ->addFilter('sku', 'LIKE', '%ak%')
            ->addFilter('name', 'LIKE', 'Akeneo%')
            ->addFilter('family', 'IN', [14])
            ->addSorter('completeness', 'ASC');

        $results = $pqb->getQueryBuilder()->getQuery()->execute();
        echo count($results).PHP_EOL;
    }

    /**
     * @return DumperInterface
     */
    protected function getProductQueryFactory()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.query.product_query_factory');
    }
}
