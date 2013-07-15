<?php

namespace Oro\Bundle\NotificationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class ExtractEventNamesCommand
 * Console command implementation
 *
 * @package Oro\Bundle\NotificationBundle\Command
 */
class ExtractEventNamesCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:notification:init');
        $this->setDescription('Extract event names from files to db');

        $this->addOption(
            'oro-only',
            'o',
            InputOption::VALUE_NONE,
            'If set, scan only Oro Bundles'
        );

        $this->addArgument(
            'bundle',
            InputArgument::OPTIONAL,
            'If set - scan only this bundle'
        );
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        /** @var Oro\Bundle\NotificationBundle\Provider\EventNamesExtractor $extractor */
        $extractor = $this->getContainer()->get('oro_notification.event_names.extractor');

        $dirs = $this->getBundleDirs($input->getArgument('bundle'), $input->getOption('oro-only'));
        $dirsCount = count($dirs);

        $i = 1;
        foreach ($dirs as $directory) {
            $output->writeln('[' . sprintf('%d%%', 100*($i++) / $dirsCount) . '] ' . $directory);
            $extractor->extract($directory);
            $extractor->dumpToDb();
        }

        $output->writeln('Completed');
    }

    /**
     * Return bundle dirs
     *
     * @param string|null $bundleName
     * @param bool $oroOnly
     * @return array|bool
     */
    public function getBundleDirs($bundleName = null, $oroOnly = false)
    {
        $directories = false;
        $bundles = $this->getContainer()->get('kernel')->getBundles();

        foreach ($bundles as $bundle) {
            if ($oroOnly && substr($bundle->getName(), 0, 3) != 'Oro') {
                continue;
            }

            /** @var $bundle \Symfony\Component\HttpKernel\Bundle\BundleInterface  */
            $directories[$bundle->getName()] = $bundle->getPath();
        }

        return !is_null($bundleName) && isset($directories[$bundleName]) ? array($directories[$bundleName]) : $directories;
    }
}
