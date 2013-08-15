<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class FindExtendCommand extends ContainerAwareCommand
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @var ConfigManager
     */
    protected $configManager;

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
     * @param  InputInterface $input
     * @param  OutputInterface $output
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        $this->extendManager = $this->getContainer()->get('oro_entity_extend.extend.extend_manager');
        $this->configManager = $this->getContainer()->get('oro_entity_config.config_manager');

        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        foreach ($kernel->getBundles() as $bundle) {
            $path = $bundle->getPath() . '/Resources/config/entity_extend.yml';
            if (is_file($path)) {
                $config = Yaml::parse(realpath($path));

                foreach ($config as $entityName => $entityConfig) {
                    $this->parseEntity($entityName, $entityConfig);
                }

                $this->configManager->flush();

                $output->writeln('Done');
            }
        }
    }

    /**
     * @param $entityName
     * @param $entityConfig
     * @throws \InvalidArgumentException
     */
    protected function parseEntity($entityName, $entityConfig)
    {
        if (!$this->configManager->isConfigurable($entityName)) {
            $this->createEntityModel($entityName, $entityConfig);
        }

        foreach ($entityConfig['fields'] as $fieldName => $fieldConfig) {
            if ($this->configManager->isConfigurable($entityName, $fieldName)) {
                throw new \InvalidArgumentException(
                    sprintf('Field "%s" for Entity "%s" already added', $entityName, $fieldName)
                );
            }

            $mode = isset($fieldConfig['mode']) ? $fieldConfig['mode'] : ConfigModelManager::MODE_DEFAULT;
            $this->extendManager->getExtendFactory()->createField($entityName, $fieldName, $fieldConfig, $mode);
        }
    }

    /**
     * @param $entityName
     * @param $entityConfig
     */
    protected function createEntityModel($entityName, $entityConfig)
    {
        $mode = isset($entityConfig['mode']) ? $entityConfig['mode'] : ConfigModelManager::MODE_DEFAULT;

        $this->configManager->createConfigEntityModel($entityName, $mode);

        $doctrineMetadata = $this->configManager->getEntityManager()->getClassMetadata($entityName);
        foreach ($doctrineMetadata->getFieldNames() as $fieldName) {
            $type = $doctrineMetadata->getTypeOfField($fieldName);
            $this->configManager->createConfigFieldModel($doctrineMetadata->getName(), $fieldName, $type);
        }

        foreach ($doctrineMetadata->getAssociationNames() as $fieldName) {
            $type = $doctrineMetadata->isSingleValuedAssociation($fieldName) ? 'ref-one' : 'ref-many';
            $this->configManager->createConfigFieldModel($doctrineMetadata->getName(), $fieldName, $type);
        }
    }
}
