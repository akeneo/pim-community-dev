<?php

namespace Pim\Bundle\ProductBundle\Command;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
            ->addOption(
                'channels',
                null,
                InputOption::VALUE_OPTIONAL,
                'list of channels'
            )
            ->addOption(
                'locales',
                null,
                InputOption::VALUE_OPTIONAL,
                'list of locales'
            )
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
        $forced   = $input->getOption('forced');
        $channels = $input->getOption('channels');
        $locales  = $input->getOption('locales');

        $this->calculator = $this->getCompletenessCalculator();

        // define channels
        if ($channels !== null) {
            $channels = explode(',', $channels);
            $channels = $this->getChannelManager()->getChannels(array('code' => $channels));

            $this->calculator->setChannels($channels);
        }

        // define locales
        if ($locales !== null) {
            $locales = explode(',', $locales);
            $locales = $this->getLocaleManager()->getLocales(array('code' => $locales));

            $this->calculator->setLocales($locales);
        }

        // define the products where the completeness must be recalculated
        $products = $this->getProductManager()->getFlexibleRepository()->findAll();

        // call calculator
        $completenesses = $this->calculator->calculate($products);

        // persists product completeness entities
        foreach ($completenesses as $productCompleteness) {
            foreach ($productCompleteness as $completeness) {
                $this->getEntityManager()->persist($completeness);
            }
        }

        $this->getEntityManager()->flush();
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
