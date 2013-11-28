<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\EntityManager;

use Metadata\MetadataFactory;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\EntityConfigBundle\Exception\LogicException;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Oro\Bundle\EntityConfigBundle\Metadata\FieldMetadata;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderBag;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;

use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewFieldConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

/**
 * @SuppressWarnings(PHPMD)
 */
class ConfigManager
{
    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ConfigCache
     */
    protected $cache;

    /**
     * @var AuditManager
     */
    protected $auditManager;

    /**
     * @var ConfigModelManager
     */
    protected $modelManager;

    /**
     * @var ServiceLink
     */
    protected $providerBag;

    /**
     * @var ConfigInterface[]|ArrayCollection
     */
    protected $localCache;

    /**
     * @var ConfigInterface[]|\SplObjectStorage
     */
    protected $persistConfigs;

    /**
     * @var ConfigInterface[]|ArrayCollection
     */
    protected $originalConfigs;

    /**
     * @var ArrayCollection
     */
    protected $configChangeSets;

    /**
     * @param MetadataFactory    $metadataFactory
     * @param EventDispatcher    $eventDispatcher
     * @param ServiceLink        $providerBagLink
     * @param ConfigModelManager $modelManager
     * @param AuditManager       $auditManager
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        EventDispatcher $eventDispatcher,
        ServiceLink $providerBagLink,
        ConfigModelManager $modelManager,
        AuditManager $auditManager
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;

        $this->providerBag      = $providerBagLink;
        $this->localCache       = new ArrayCollection;
        $this->persistConfigs   = new \SplObjectStorage();
        $this->originalConfigs  = new ArrayCollection;
        $this->configChangeSets = new ArrayCollection;

        $this->modelManager = $modelManager;
        $this->auditManager = $auditManager;
    }

    /**
     * @param ConfigCache $cache
     */
    public function setCache(ConfigCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->modelManager->getEntityManager();
    }

    /**
     * @return ConfigProviderBag
     */
    public function getProviderBag()
    {
        return $this->providerBag->getService();
    }

    /**
     * @return ConfigProvider[]|ArrayCollection
     */
    public function getProviders()
    {
        return $this->getProviderBag()->getProviders();
    }

    /**
     * @param $scope
     * @return ConfigProvider
     */
    public function getProvider($scope)
    {
        return $this->getProviderBag()->getProvider($scope);
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $className
     * @return EntityMetadata|null
     */
    public function getEntityMetadata($className)
    {
        return class_exists($className) ? $this->metadataFactory->getMetadataForClass($className) : null;
    }

    /**
     * @param $className
     * @param $fieldName
     * @return null|FieldMetadata
     */
    public function getFieldMetadata($className, $fieldName)
    {
        $metadata = $this->getEntityMetadata($className);
        if ($metadata && isset ($metadata->propertyMetadata[$fieldName])) {
            return $metadata->propertyMetadata[$fieldName];
        }

        return null;
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function hasConfig($className, $fieldName = null)
    {
        if (!$this->modelManager->checkDatabase()) {
            return false;
        }

        $result = $this->cache->getConfigurable($className, $fieldName);
        if ($result === false) {
            $result = (bool) $this->modelManager->findModel($className, $fieldName) ? : null;

            $this->cache->setConfigurable($result, $className, $fieldName);
        }

        return $result;
    }

    /**
     * @param $scope
     * @param $className
     * @return array
     */
    public function getIds($scope, $className = null)
    {
        if (!$this->modelManager->checkDatabase()) {
            return array();
        }

        $entityModels = $this->modelManager->getModels($className);

        return array_map(
            function (AbstractConfigModel $model) use ($scope) {
                if ($model instanceof FieldConfigModel) {
                    return new FieldConfigId(
                        $model->getEntity()->getClassName(),
                        $scope,
                        $model->getFieldName(),
                        $model->getType()
                    );
                } else {
                    return new EntityConfigId($model->getClassName(), $scope);
                }
            },
            $entityModels
        );
    }

    /**
     * @param ConfigIdInterface $configId
     * @throws RuntimeException
     * @throws LogicException
     * @return ConfigInterface
     */
    public function getConfig(ConfigIdInterface $configId)
    {
        if ($this->localCache->containsKey($configId->toString())) {
            return $this->localCache->get($configId->toString());
        }

        if (!$this->modelManager->checkDatabase()) {
            throw new LogicException(
                'Database is not synced, if you use ConfigManager, when a db schema may be hasn\'t synced.'
                . ' check it by ConfigManager::modelManager::checkDatabase'
            );
        }

        if (!$this->hasConfig($configId->getClassName())) {
            throw new RuntimeException(sprintf('Entity "%s" is not configurable', $configId->getClassName()));
        }

        $resultConfig = null !== $this->cache
            ? $this->cache->loadConfigFromCache($configId)
            : null;

        if (!$resultConfig) {
            $model = $this->modelManager->getModelByConfigId($configId);

            $config = new Config($this->getConfigIdByModel($model, $configId->getScope()));
            $config->setValues($model->toArray($configId->getScope()));

            if (null !== $this->cache) {
                $this->cache->putConfigInCache($config);
            }

            $resultConfig = $config;
        }

        //local cache
        $this->localCache->set($resultConfig->getId()->toString(), $resultConfig);

        //for calculate change set
        $this->originalConfigs->set($resultConfig->getId()->toString(), clone $resultConfig);

        return $resultConfig;
    }


    /**
     * @param ConfigIdInterface $configId
     */
    public function clearCache(ConfigIdInterface $configId)
    {
        if ($this->cache) {
            $this->cache->removeConfigFromCache($configId);
        }
    }

    /**
     * Remove All cache
     */
    public function clearCacheAll()
    {
        if ($this->cache) {
            $this->cache->removeAll();
        }
    }

    /**
     * Remove All Configurable cache
     */
    public function clearConfigurableCache()
    {
        if ($this->cache) {
            $this->cache->removeAllConfigurable();
        }
        $this->modelManager->clearCheckDatabase();
    }

    /**
     * @param ConfigInterface $config
     */
    public function persist(ConfigInterface $config)
    {
        $this->persistConfigs->attach($config);
    }

    /**
     * @param ConfigInterface $config
     * @return ConfigInterface
     */
    public function merge(ConfigInterface $config)
    {
        $config = $this->doMerge($config);
        $this->persistConfigs->attach($config);

        return $config;
    }

    public function flush()
    {
        $models = array();

        foreach ($this->persistConfigs as $config) {
            $this->calculateConfigChangeSet($config);

            $this->eventDispatcher->dispatch(Events::PRE_PERSIST_CONFIG, new PersistConfigEvent($config, $this));

            if (isset($models[$config->getId()->toString()])) {
                $model = $models[$config->getId()->toString()];
            } else {
                $model = $this->modelManager->getModelByConfigId($config->getId());

                $models[$config->getId()->toString()] = $model;
            }

            //TODO::refactoring
            $serializableValues = $this->getProvider($config->getId()->getScope())
                ->getPropertyConfig()
                ->getSerializableValues($config->getId());
            $model->fromArray($config->getId()->getScope(), $config->all(), $serializableValues);

            if ($this->cache) {
                $this->cache->removeConfigFromCache($config->getId());
                $this->cache->removeAllConfigurable();
            }
        }

        $this->auditManager->log();

        foreach ($models as $model) {
            $this->getEntityManager()->persist($model);
        }

        $this->getEntityManager()->flush();

        $this->persistConfigs   = new \SplObjectStorage();
        $this->configChangeSets = new ArrayCollection;
    }


    /**
     * @param ConfigInterface $config
     * @SuppressWarnings(PHPMD)
     */
    public function calculateConfigChangeSet(ConfigInterface $config)
    {
        $originConfigValue = array();
        if ($this->originalConfigs->containsKey($config->getId()->toString())) {
            $originConfig      = $this->originalConfigs->get($config->getId()->toString());
            $originConfigValue = $originConfig->all();
        }

        foreach ($config->all() as $key => $value) {
            if (!isset($originConfigValue[$key])) {
                $originConfigValue[$key] = null;
            }
        }

        $diffNew = array_udiff_assoc(
            $config->all(),
            $originConfigValue,
            function ($a, $b) {
                return ($a == $b) ? 0 : 1;
            }
        );

        $diffOld = array_udiff_assoc(
            $originConfigValue,
            $config->all(),
            function ($a, $b) {
                return ($a == $b) ? 0 : 1;
            }
        );

        $diff = array();
        foreach ($diffNew as $key => $value) {
            $oldValue   = isset($diffOld[$key]) ? $diffOld[$key] : null;
            $diff[$key] = array($oldValue, $value);
        }


        if (!$this->configChangeSets->containsKey($config->getId()->toString())) {
            $this->configChangeSets->set($config->getId()->toString(), array());
        }

        if (count($diff)) {
            $changeSet = array_merge($this->configChangeSets->get($config->getId()->toString()), $diff);
            $this->configChangeSets->set($config->getId()->toString(), $changeSet);
        }
    }

    /**
     * @param callable $filter
     * @return ConfigInterface[]|ArrayCollection
     */
    public function getUpdateConfig(\Closure $filter = null)
    {
        $result = array();
        foreach ($this->persistConfigs as $element) {
            $result[] = $element;
        }

        return $filter ? array_filter($result, $filter) : $result;
    }

    /**
     * @param ConfigInterface $config
     * @return array
     */
    public function getConfigChangeSet(ConfigInterface $config)
    {
        return $this->configChangeSets->containsKey($config->getId()->toString())
            ? $this->configChangeSets->get($config->getId()->toString())
            : array();
    }

    /**
     * Checks if the configuration model for the given class exists
     *
     * @param string $className
     * @return bool
     */
    public function hasConfigEntityModel($className)
    {
        return null !== $this->modelManager->findModel($className);
    }

    /**
     * Checks if the configuration model for the given field exist
     *
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function hasConfigFieldModel($className, $fieldName)
    {
        return null !== $this->modelManager->findModel($className, $fieldName);
    }

    /**
     * TODO:: check class name for custom entity
     *
     * @param string $className
     * @param string $mode
     * @return EntityConfigModel
     */
    public function createConfigEntityModel($className, $mode = ConfigModelManager::MODE_DEFAULT)
    {
        if (!$entityModel = $this->modelManager->findModel($className)) {
            $entityModel = $this->modelManager->createEntityModel($className, $mode);

            foreach ($this->getProviders() as $provider) {

                $metadata      = $this->getEntityMetadata($className);
                $defaultValues = array();
                if ($metadata && isset($metadata->defaultValues[$provider->getScope()])) {
                    $defaultValues = $metadata->defaultValues[$provider->getScope()];
                }

                $entityId = new EntityConfigId($className, $provider->getScope());
                $config   = $provider->createConfig($entityId, $defaultValues);

                $this->localCache->set($config->getId()->toString(), $config);
            }

            $this->eventDispatcher->dispatch(
                Events::NEW_ENTITY_CONFIG_MODEL,
                new NewEntityConfigModelEvent($entityModel, $this)
            );
        }

        return $entityModel;
    }

    /**
     * @param string $className
     *
     * @TODO: need refactoring. Join updateConfigEntityModel and updateConfigFieldModel.
     *        may be need introduce MetadataWithDefaultValuesInterface
     *        need handling for removed values
     *        need refactor getConfig
     *        need to find out more appropriate name for this method
     */
    public function updateConfigEntityModel($className)
    {
        $metadata = $this->getEntityMetadata($className);
        foreach ($this->getProviders() as $provider) {
            $scope = $provider->getScope();
            // try to get default values from annotation
            $defaultValues = array();
            if (isset($metadata->defaultValues[$scope])) {
                $defaultValues = $metadata->defaultValues[$scope];
            }
            // combine them with default values from config file
            $defaultValues = array_merge(
                $provider->getPropertyConfig()->getDefaultValues(),
                $defaultValues
            );

            // set missing values with default ones
            $hasChanges = false;
            $config = $provider->getConfig($className);
            foreach ($defaultValues as $code => $value) {
                if (!$config->has($code)) {
                    $config->set($code, $value);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                $provider->persist($config);
            }
        }
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @param string $fieldType
     * @param string $mode
     * @return FieldConfigModel
     */
    public function createConfigFieldModel($className, $fieldName, $fieldType, $mode = ConfigModelManager::MODE_DEFAULT)
    {
        if (!$fieldModel = $this->modelManager->findModel($className, $fieldName)) {
            $fieldModel = $this->modelManager->createFieldModel($className, $fieldName, $fieldType, $mode);

            foreach ($this->getProviders() as $provider) {
                $defaultValues = array();
                $metadata      = $this->getFieldMetadata($className, $fieldName);
                if ($metadata && isset($metadata->defaultValues[$provider->getScope()])) {
                    $defaultValues = $metadata->defaultValues[$provider->getScope()];
                }

                $fieldId = new FieldConfigId($className, $provider->getScope(), $fieldName, $fieldType);
                $config  = $provider->createConfig($fieldId, $defaultValues);

                $this->localCache->set($config->getId()->toString(), $config);
            }

            $this->eventDispatcher->dispatch(
                Events::NEW_FIELD_CONFIG_MODEL,
                new NewFieldConfigModelEvent($fieldModel, $this)
            );
        }

        return $fieldModel;
    }

    /**
     * @param string $className
     * @param string $fieldName
     *
     * @TODO: need refactoring. Join updateConfigEntityModel and updateConfigFieldModel.
     *        may be need introduce MetadataWithDefaultValuesInterface
     *        need handling for removed values
     *        need refactor getConfig
     *        need to find out more appropriate name for this method
     */
    public function updateConfigFieldModel($className, $fieldName)
    {
        $metadata = $this->getFieldMetadata($className, $fieldName);
        foreach ($this->getProviders() as $provider) {
            $scope = $provider->getScope();
            // try to get default values from annotation
            $defaultValues = array();
            if (isset($metadata->defaultValues[$scope])) {
                $defaultValues = $metadata->defaultValues[$scope];
            }
            // combine them with default values from config file
            $defaultValues = array_merge(
                $provider->getPropertyConfig()->getDefaultValues(),
                $defaultValues
            );

            // set missing values with default ones
            $hasChanges = false;
            $config = $provider->getConfig($className, $fieldName);
            foreach ($defaultValues as $code => $value) {
                if (!$config->has($code)) {
                    $config->set($code, $value);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                $provider->persist($config);
            }
        }
    }

    /**
     * @param ConfigInterface $config
     * @return ConfigInterface
     */
    private function doMerge(ConfigInterface $config)
    {
        foreach ($this->persistConfigs as $persistConfig) {
            if ($config->getId()->toString() == $persistConfig->getId()->toString()) {
                $config = array_merge($persistConfig->all(), $config->all());

                break;
            }
        }

        return $config;
    }

    private function getConfigIdByModel(AbstractConfigModel $model, $scope)
    {
        if ($model instanceof FieldConfigModel) {
            return new FieldConfigId(
                $model->getEntity()->getClassName(),
                $scope,
                $model->getFieldName(),
                $model->getType()
            );
        } else {
            return new EntityConfigId(
                $model->getClassName(),
                $scope
            );
        }
    }
}
