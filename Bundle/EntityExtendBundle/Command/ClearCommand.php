<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class ClearCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('oro:entity-extend:clear')
            ->setDescription('Clear extend cache folder');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        $dumper = $this->getContainer()->get('oro_entity_extend.tools.dumper');
        $dumper->clear();

        $output->writeln('Done');
    }
}
