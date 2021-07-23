<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Command;

use AkeneoMeasureBundle\Installer\MeasurementInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected static $defaultName = 'database:install';

    /** @var MeasurementInstaller */
    private $installer;

    public function __construct(MeasurementInstaller $installer)
    {
        parent::__construct();
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Install the micro backend database');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Installing Micro Application.</info>');
        $output->writeln('');

        $this->installer->createMeasurementTableAndStandardMeasurementFamilies();

        return 0;
    }
}
