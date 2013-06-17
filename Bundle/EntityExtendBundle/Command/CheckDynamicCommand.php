<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\EntityExtendBundle\Tools\Schema;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDynamicCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName('oro:entity-extend:check-dynamic')
            ->setDescription('Check ability to regenerate schema');
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

        /** @var Schema $schema */
        $schema = $this->getContainer()->get('oro_entity_extend.tools.schema');

        $output->writeln("");
        if ($schema->checkDynamicBackend()) {
            $output->writeln("<info>Ok</info> app can regenerate schema");
        } else {
            $output->writeln("<error>NO</error> app cannot regenerate schema");
        }
    }
}
