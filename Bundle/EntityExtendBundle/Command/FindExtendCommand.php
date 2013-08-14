<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class FindExtendCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName('oro:entity-extend:find-entity')
            ->setDescription('Find description about custom entity and custom field');
    }

    /**
     * Runs command
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        /** @var ExtendManager $extendManager */
        $extendManager = $this->getContainer()->get('oro_entity_extend.extend.extend_manager');
        /** @var \Oro\Bundle\EntityConfigBundle\Config\ConfigManager $configManager */
        $configManager = $this->getContainer()->get('oro_entity_config.config_manager');

        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        foreach ($kernel->getBundles() as $bundle) {
            $path = $bundle->getPath() . '/Resources/config/entity_extend.yml';
            if (is_file($path)) {
                $config = Yaml::parse(realpath($path));

                foreach ($config as $entityName => $entityConfig) {
                    if ($entityConfig['type'] == 'Extend') {
                        if (!class_exists($entityName)) {
                            throw new \InvalidArgumentException(sprintf('Entity "%s" is not found'));
                        }

                        if (!$configManager->isConfigurable($entityName)) {
                            $mode = isset($entityConfig['mode']) ? $entityConfig['mode'] : 'hidden';

                            $configManager->createConfigEntityModel($entityName);
                            $doctrineMetadata = $configManager->getEntityManager()->getClassMetadata($entityName);
                            foreach ($doctrineMetadata->getFieldNames() as $fieldName) {
                                $type = $doctrineMetadata->getTypeOfField($fieldName);
                                $mode = isset($entityConfig['mode']) ? $entityConfig['mode'] : 'hidden';
                                $configManager->createConfigFieldModel(
                                    $doctrineMetadata->getName(),
                                    $fieldName,
                                    $type,
                                    $mode
                                );
                            }

                            foreach ($doctrineMetadata->getAssociationNames() as $fieldName) {
                                $type = $doctrineMetadata->isSingleValuedAssociation(
                                    $fieldName
                                ) ? 'ref-one' : 'ref-many';
                                $configManager->createConfigFieldModel(
                                    $doctrineMetadata->getName(),
                                    $fieldName,
                                    $type,
                                    $mode
                                );
                            }

                            $extendManager->getExtendFactory()->createEntity($entityName, $mode);

                            $output->writeln(sprintf('Entity "%s" was added', $entityName));
                        }
                    }

                    if ($entityConfig['type'] == 'Custom') {
                        $mode = isset($entityConfig['mode']) ? $entityConfig['mode'] : 'default';
                        $extendManager->getExtendFactory()->createEntity($entityName, $mode);

                        $output->writeln(sprintf('Entity "%s" was added', $entityName));
                    }

                    foreach ($entityConfig['fields'] as $fieldName => $fieldConfig) {
                        $mode = isset($fieldConfig['mode']) ? $fieldConfig['mode'] : 'default';
                        $extendManager->getExtendFactory()->createField($entityName, $fieldName, $fieldConfig, $mode);

                        $output->writeln(sprintf('Field for "%s" Entity "%s" was added', $fieldName, $entityName));
                    }
                }

                $configManager->flush();
            }
        }
    }
}
