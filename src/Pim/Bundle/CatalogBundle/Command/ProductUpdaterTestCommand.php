<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO : temporary command to explain / discuss the archi, will be droped before merge
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdaterTestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:debug:product-updater-test');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // select via a cleaner API
        $pqbFactory = $this->getContainer()->get('pim_catalog.doctrine.query.product_query_factory');
        $pqb = $pqbFactory->create()
            ->addFilter('family', 'IN', [14])
            ->addFilter('main_color', 'IN', [41]);

        // TODO : this execution part is still weird, we could "wrap" the execution but we need to cover
        // both ORM/MongoODM hydration
        $products = $pqb->getQueryBuilder()->getQuery()->getResult();
        $output->writeln(sprintf("<info>%d selected<info>", count($products)));

        // update via another clean API FTW
        $updater = $this->getContainer()->get('pim_catalog.updater.product');
        $updater
            ->setValue($products, 'name', 'new name')
            ->setValue($products, 'description', 'new desc', ['locale' => 'en_US', 'scope' => 'mobile'])
            ->copyValue($products, 'description', 'description', ['from_locale' => 'en_US', 'from_scope' => 'mobile', 'to_locale' => 'en_US', 'to_scope' => 'print']);

        // flush with doctrine
        $om = $this->getContainer()->get('pim_catalog.object_manager.product');
        $om->flush();
        $output->writeln(sprintf("<info>%d updated<info>", count($products)));
    }
}
