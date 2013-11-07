<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Calculate the completeness of the products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateCompletenessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:completeness:calculate')
            ->setDescription('Launch the product completeness calculation')
            ->addArgument('size', InputArgument::OPTIONAL, 'Max number of completenesses to calculate');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCompletenessRepository()->createAllCompletenesses(
            $input->getArgument('size')
        );
    }

    /**
     * Get the completeness repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCompletenessRepository()
    {
        return $this
            ->getEntityManager()
            ->getRepository('PimCatalogBundle:Completeness');
    }

    /**
     * Get the entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
