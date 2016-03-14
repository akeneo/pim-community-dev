<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes a list of queries to updates the normalized data of a
 * product document when related entities are modified.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateNormalizedProductDataCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:update-normalized-data')
            ->setDescription('Updates the normalized data of a product when related entities are modified')
            ->addArgument(
                'queries',
                InputArgument::REQUIRED,
                'The list of queries to execute (JSON formatted)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $documentManager = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $productClass    = $this->getContainer()->getParameter('pim_catalog.entity.product.class');
        $queries         = json_decode($input->getArgument('queries'), true);

        if (null === $queries) {
            throw new \InvalidArgumentException('There is no valid queries to execute or the JSON syntax is wrong');
        }

        $collection = $documentManager->getDocumentCollection($productClass);

        foreach ($queries as $query) {
            list($query, $compObject, $options) = $query;

            // It is possible to define an option here to avoid a MongoDB timeout when having a lot of data.
            // $options['socketTimeoutMS'] = -1;
            $collection->update($query, $compObject, $options);
        }
    }
}
