<?php

namespace Oro\Bundle\EntityConfigBundle\Command;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

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

        /** @var ClassMetadataInfo $doctrineMetadata */
        foreach ($this->getConfigManager()->em()->getMetadataFactory()->getAllMetadata() as $doctrineMetadata) {
            if ($this->getConfigManager()->isConfigurable($doctrineMetadata->getName())) {
                $this->getConfigManager()->createConfigEntity($doctrineMetadata->getName());
            }
        }

        $this->getConfigManager()->flush();

        $output->writeln('Completed');
    }
}
