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
 * You can add options :
 *     - channels : List of channels code on which you want to calculate completeness
 *     - locales  : List of locales code on which you want to calculate completeness
 *     - forced   : Predicate allowing to forced to recalculate a value even if don't need to be reindexed
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
            ->setDescription('Launch the product completeness calculator')
            ->addOption('channels', null, InputOption::VALUE_OPTIONAL, 'list of channels')
            ->addOption('locales', null, InputOption::VALUE_OPTIONAL, 'list of locales')
            ->addOption(
                'forced',
                null,
                InputOption::VALUE_NONE,
                'if defined, the calculator doesn\'t take care about reindex'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channels = $input->getOption('channels');
        $locales  = $input->getOption('locales');
        $forced   = $input->getOption('forced');

        $batchCalculator = $this->getContainer()->get('pim_product.calculator.batch_completeness');
        $batchCalculator->execute();

//         $this->calculator = $this->getCompletenessCalculator();

//         // define channels
//         if ($channels !== null) {
//             $channels = explode(',', $channels);
//             $channels = $this->getChannelManager()->getChannels(array('code' => $channels));

//             $this->calculator->setChannels($channels);
//         }

//         // define locales
//         if ($locales !== null) {
//             $locales = explode(',', $locales);
//             $locales = $this->getLocaleManager()->getLocales(array('code' => $locales));

//             $this->calculator->setLocales($locales);
//         }

//         // TODO : define the products where the completeness must be recalculated
//         // depending of the forced option
//         $products = $this->getProductManager()->getFlexibleRepository()->findAll();

//         // Call calculator and persists entities
//         $completenesses = $this->calculator->calculate($products);
//         foreach ($completenesses as $sku => $productCompleteness) {
//             foreach ($productCompleteness as $completeness) {
//                 $this->getEntityManager()->persist($completeness);
//             }
//         }

//         $this->getEntityManager()->flush();
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
