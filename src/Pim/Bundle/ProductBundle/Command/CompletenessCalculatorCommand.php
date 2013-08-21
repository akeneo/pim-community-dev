<?php

namespace Pim\Bundle\ProductBundle\Command;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CompletenessCalculatorCommand extends ContainerAwareCommand
{
    /**
     * @var array $channels
     */
    protected $channels;

    /**
     * @var array $locales
     */
    protected $locales;

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
                InputOption::VALUE_IS_ARRAY,
                InputOption::VALUE_OPTIONAL,
                'list of channels'
            )
            ->addOption(
                'locales',
                InputOption::VALUE_IS_ARRAY,
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
        $forced         = $input->getOption('forced');
        $this->channels = $input->getOption('channels');
        $this->locales  = $input->getOption('locales');

        if ($this->channels === null) {
            $this->channels = $this->getChannels();
        } else {
            // transforms string to array
            // transforms code to channel
        }

        if ($this->locales === null) {
            $this->locales = $this->getLocales();
        } else {
            // transforms string to array
            // transforms code to locale
        }

        $this->calculator = $this->getCompletenessCalculator();
        $this->calculator->setChannels($channels);
        $this->calculator->setLocales($locales);

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
     * Get all channels
     *
     * @return \Doctrine\Common\Persistence\ArrayCollection
     */
    protected function getChannels()
    {
        return $this->getChannelManager()->getChannels();
    }
}
