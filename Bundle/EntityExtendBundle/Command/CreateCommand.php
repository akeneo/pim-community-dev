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

class CreateCommand extends ContainerAwareCommand
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
            ->setName('oro:entity-extend:create')
            ->setDescription('Find description about custom entities and fields');
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

        $this->getApplication()->find('oro:entity-extend:update')->run($input, $output);
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
            $this->setDefaultConfig($entityConfig, $entityName);

            if (!class_exists($entityName)) {
                $config = array(
                    'type' => 'integer',
                );

                $this->extendManager->getExtendFactory()->createField($entityName, 'id', $config);

                $entityConfig->set('owner', ExtendManager::OWNER_CUSTOM);
                $entityConfig->set('is_extend', true);
            }
        }

        foreach ($entityConfig['fields'] as $fieldName => $fieldConfig) {
            if ($this->configManager->isConfigurable($entityName, $fieldName)) {
                throw new \InvalidArgumentException(
                    sprintf('Field "%s" for Entity "%s" already added', $entityName, $fieldName)
                );
            }

            $mode = isset($fieldConfig['mode']) ? $fieldConfig['mode'] : ConfigModelManager::MODE_DEFAULT;
            $this->extendManager->getExtendFactory()->createField($entityName, $fieldName, $fieldConfig, $mode);

            $this->setDefaultConfig($entityConfig, $entityName, $fieldName);
        }
    }

    /**
     * @param $entityName
     * @param $entityConfig
     * @return void
     */
    protected function createEntityModel($entityName, $entityConfig)
    {
        $mode = isset($entityConfig['mode']) ? $entityConfig['mode'] : ConfigModelManager::MODE_DEFAULT;

        $this->configManager->createConfigEntityModel($entityName, $mode);

        if (class_exists($entityName)) {
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

    /**
     * @param array $options
     * @param string $entityName
     * @param string $fieldName
     */
    protected function setDefaultConfig($options, $entityName, $fieldName = null)
    {
        if (isset($options['configs']) && is_array($options['configs'])) {
            foreach ($options['configs'] as $scope => $values) {
                $config = $this->configManager->getProvider($scope)->getConfig($entityName, $fieldName);

                foreach ($values as $key => $value) {
                    $config->set($key, $value);
                }
            }
        }
    }
}
