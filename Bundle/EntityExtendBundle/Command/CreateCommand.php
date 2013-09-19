<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class CreateCommand extends ContainerAwareCommand
{
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
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        /** @var ConfigProvider $configProvider */
        $configProvider = $this->getContainer()->get('oro_entity_config.provider.extend');

        $this->configManager = $this->getContainer()->get('oro_entity_config.config_manager');

        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        foreach ($kernel->getBundles() as $bundle) {
            $path = $bundle->getPath() . '/Resources/config/entity_extend.yml';
            if (is_file($path)) {
                $config = Yaml::parse(realpath($path));

                foreach ($config as $className => $entityOptions) {
                    $className = class_exists($className) ? $className : 'Extend\\Entity\\' . $className;
                    $this->parseEntity($className, $entityOptions);
                }

                $this->configManager->flush();

                //fix state "Update" for existing class.
                foreach ($config as $className => $entityOptions) {
                    $className    = class_exists($className) ? $className : 'Extend\\Entity\\' . $className;
                    $entityConfig = $configProvider->getConfig($className);
                    $entityConfig->set('state', ExtendManager::STATE_ACTIVE);

                    $this->configManager->persist($entityConfig);

                    foreach ($configProvider->getConfigs($className) as $fieldConfig) {
                        $fieldConfig->set('state', ExtendManager::STATE_ACTIVE);
                        $this->configManager->persist($fieldConfig);
                    }
                }
                $this->configManager->flush();

                $output->writeln('Done');
            }
        }

        $this->configManager->clearConfigurableCache();

        $this->getApplication()->find('oro:entity-extend:update')->run($input, $output);
    }

    /**
     * @param $className
     * @throws \InvalidArgumentException
     */
    protected function checkExtend($className)
    {
        $error = false;
        if (!$this->configManager->hasConfig($className)) {
            $error = true;
        } else {
            $config = $this->configManager->getProvider('extend')->getConfig($className);
            if (!$config->is('is_extend')) {
                $error = true;
            }
        }

        if ($error) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not extended.', $className));
        }
    }

    /**
     * @param $className
     * @param $entityOptions
     * @throws \InvalidArgumentException
     */
    protected function parseEntity($className, $entityOptions)
    {
        /** @var ExtendManager $extendManager */
        $extendManager  = $this->getContainer()->get('oro_entity_extend.extend.extend_manager');
        $configProvider = $extendManager->getConfigProvider();

        if (class_exists($className)) {
            $this->checkExtend($className);
        }

        if (!$this->configManager->hasConfig($className)) {
            $this->createEntityModel($className, $entityOptions);
            $this->setDefaultConfig($entityOptions, $className);

            $entityConfig = $configProvider->getConfig($className);

            $owner = ExtendManager::OWNER_SYSTEM;
            if (isset($entityOptions['owner'])) {
                $owner = $entityOptions['owner'];
            }
            $entityConfig->set('owner', $owner);

            if (isset($entityOptions['is_extend'])) {
                $entityConfig->set('is_extend', $entityOptions['is_extend']);
            } else {
                $entityConfig->set('is_extend', false);
            }
        }

        foreach ($entityOptions['fields'] as $fieldName => $fieldConfig) {
            if ($this->configManager->hasConfig($className, $fieldName)) {
                throw new \InvalidArgumentException(
                    sprintf('Field "%s" for Entity "%s" already added', $className, $fieldName)
                );
            }

            $owner = ExtendManager::OWNER_SYSTEM;
            if (isset($fieldConfig['owner'])) {
                $owner = $fieldConfig['owner'];
            }

            $mode  = ConfigModelManager::MODE_DEFAULT;
            if (isset($fieldConfig['mode'])) {
                $mode = $fieldConfig['mode'];
            }

            $extendManager->createField($className, $fieldName, $fieldConfig, $owner, $mode);

            $this->setDefaultConfig($entityOptions, $className, $fieldName);

            $config = $configProvider->getConfig($className, $fieldName);
            $config->set('state', ExtendManager::STATE_ACTIVE);
            $this->configManager->persist($config);
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
     * @param array  $options
     * @param string $entityName
     * @param string $fieldName
     */
    protected function setDefaultConfig($options, $entityName, $fieldName = null)
    {
        if ($fieldName) {
            $config = isset($options['fields'][$fieldName]['configs'])
                ? $options['fields'][$fieldName]['configs']
                : array();
        } else {
            $config = isset($options['configs']) ? $options['configs'] : array();
        }

        foreach ($config as $scope => $values) {
            $config = $this->configManager->getProvider($scope)->getConfig($entityName, $fieldName);

            foreach ($values as $key => $value) {
                $config->set($key, $value);
            }
        }
    }
}
