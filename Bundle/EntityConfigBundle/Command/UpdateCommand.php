<?php

namespace Oro\Bundle\EntityConfigBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends BaseCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName('oro:entity-config:update')
            ->setDescription('EntityConfig configurator updater');
    }

    /**
     * Runs command
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        foreach ($this->getConfigManager()->em()->getMetadataFactory()->getAllMetadata() as $doctrineMetadata) {
            $this->getConfigManager()->initConfigByDoctrineMetadata($doctrineMetadata);
        }

        $this->getConfigManager()->flush();

        $output->writeln('Completed');
    }
}
