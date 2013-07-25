<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ValidateCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName('oro:entity-extend:validate')
            ->setDescription('Validate schema');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var ExtendManager $xm */
        $xm = $this->getContainer()->get('oro_entity_extend.extend.extend_manager');

        /** @var ConfigEntity[] $configs */
        $configs = $em->getRepository(ConfigEntity::ENTITY_NAME)->findAll();
        foreach ($configs as $config) {
            if ($xm->isExtend($config->getClassName())) {



            };
        }

        //$output->writeln('Done.');
    }
}
