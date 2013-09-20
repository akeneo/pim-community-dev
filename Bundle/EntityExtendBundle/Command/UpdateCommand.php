<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\Generator;

class UpdateCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName('oro:entity-extend:update')
            ->setDescription('Generate class and yml for doctrine');
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

        $generator = $this->getContainer()->get('oro_entity_extend.tools.generator');
        $generator->generateAll();

        $output->writeln('Done');
    }
}
