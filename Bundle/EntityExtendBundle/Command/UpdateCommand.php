<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\Generator\Generator;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends ContainerAwareCommand
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
            ->setName('oro:entity-extend:update')
            ->setDescription('Generate class and yml for doctrine');
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

        /** @var OroEntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Generator $generator */
        $generator = $this->getContainer()->get('oro_entity_extend.tools.generator');

        $generator->initBase();

        /** @var EntityConfigModel[] $configs */
        $configs = $em->getRepository(EntityConfigModel::ENTITY_NAME)->findAll();
        foreach ($configs as $config) {
            $generator->generate($config->getClassName());
        }

        $output->writeln('Done');
    }
}
