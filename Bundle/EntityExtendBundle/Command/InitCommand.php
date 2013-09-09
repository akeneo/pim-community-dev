<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\Export\Driver\YamlExporter;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

class InitCommand extends ContainerAwareCommand
{
    /**
     * @var OroEntityManager
     */
    protected $em;

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName('oro:entity-extend:init')
            ->setDescription('Find extend entity and dump metadata to yml');
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

        /** @var ClassMetadataInfo $metadata */
        foreach ($em->getMetadataFactory()->getAllMetadata() as $metadata) {
            $extendEntities = array();
            if ($em->getExtendManager()->isExtend($metadata->getName())) {
                $extendEntities[] = $metadata;
                var_dump($metadata->getName());
            }

            if (count($extendEntities)) {
                $exporter = new YamlExporter();
                $exporter->setMetadata($extendEntities);
                $exporter->setExtension('.yml');
                $exporter->setOverwriteExistingFiles(true);
                $exporter->setOutputDir($this->getContainer()->getParameter('kernel.root_dir') . '/entities/Yaml/');

                $exporter->export();
            }
        }

        $output->writeln('Done');
    }
}
 