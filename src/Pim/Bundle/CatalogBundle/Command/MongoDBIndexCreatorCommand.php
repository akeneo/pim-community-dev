<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command creating indexes on MongoDB for Product collection
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MongoDBIndexCreatorCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:mongodb:index-fields')
            ->setDescription('Create MongoDB indexes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storageDriver = $this->getContainer()->getParameter('pim_catalog_product_storage_driver');

        if ($storageDriver !== 'doctrine/mongodb-odm') {
            $output->writeln('<error>This command could be only launched on mongodb storage</error>');

            return 1;
        }

        $indexCreator = $this->getIndexCreator();
        $indexCreator->ensureUniqueAttributesIndexes();
        $indexCreator->ensureCompletenessesIndexes();
        $indexCreator->ensureAttributesIndexes();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexCreator
     */
    protected function getIndexCreator()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.index_creator');
    }
}
