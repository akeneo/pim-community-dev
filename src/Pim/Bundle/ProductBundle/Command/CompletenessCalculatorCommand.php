<?php

namespace Pim\Bundle\ProductBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Calculate the completeness of the products
 *
 * Launch command :
 * php app/console pim:product:completeness-calculator
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculatorCommand extends ContainerAwareCommand
{
    /**
     * @var \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator
     */
    protected $calculator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:completeness-calculator')
            ->setDescription('Launch the product completeness calculator');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchCalculator = $this->getContainer()->get('pim_product.calculator.batch_completeness');
        $batchCalculator->execute();
    }

    /**
     * Get the product completeness calculator
     *
     * @return \Pim\Bundle\ProductBundle\Calculator\CompletenessCalculator
     */
    protected function getCompletenessCalculator()
    {
        return $this->getContainer()->get('pim_product.calculator.completeness');
    }

    /**
     * Get the channel manager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\ChannelManager
     */
    protected function getChannelManager()
    {
        return $this->getContainer()->get('pim_product.manager.channel');
    }

    /**
     * Get the locale manager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->getContainer()->get('pim_product.manager.locale');
    }

    /**
     * Get the product manager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\ProductManager
     */
    protected function getProductManager()
    {
        return $this->getContainer()->get('pim_product.manager.product');
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
