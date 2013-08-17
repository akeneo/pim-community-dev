<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Metadata\MetadataFactory;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\EntityConfigBundle\Exception\LogicException;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Oro\Bundle\EntityConfigBundle\Metadata\FieldMetadata;

use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
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
     * @var ServiceLink
     */
    protected $emLink;

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
     * @param MetadataFactory $metadataFactory
     * @param EventDispatcher $eventDispatcher
     * @param ServiceLink     $providerBagLink
     * @param ServiceLink     $emLink
     * @param ServiceLink     $securityLink
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        EventDispatcher $eventDispatcher,
        ServiceLink $providerBagLink,
        ServiceLink $emLink,
        ServiceLink $securityLink
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->emLink          = $emLink;
        $this->eventDispatcher = $eventDispatcher;

        $this->providerBag      = $providerBagLink;
        $this->localCache       = new ArrayCollection;
        $this->persistConfigs   = new \SplObjectStorage();
        $this->originalConfigs  = new ArrayCollection;
        $this->configChangeSets = new ArrayCollection;

        $this->modelManager = new ConfigModelManager($emLink);
        $this->auditManager = new AuditManager($this, $securityLink);
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
     * @return bool
     */
    public function checkDatabase()
    {
        $tables = $this->getEntityManager()->getConnection()->getSchemaManager()->listTableNames();
        $table  = $this->getEntityManager()->getClassMetadata(EntityConfigModel::ENTITY_NAME)->getTableName();

        return in_array($table, $tables);
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function isConfigurable($className, $fieldName = null)
    {
        return (bool) $this->modelManager->findModel($className, $fieldName);
    }

    /**
     * @param $scope
     * @param $className
     * @return array
     */
    public function getIds($scope, $className = null)
    {
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
     * @return bool
     */
    public function hasConfig(ConfigIdInterface $configId)
    {
        if ($this->localCache->containsKey($configId->getId())) {
            return true;
        }

        if (null !== $this->cache
            && $config = $this->cache->loadConfigFromCache($configId)
        ) {
            return true;
        }

        return (bool) $this->modelManager->getModelByConfigId($configId);
    }

    /**
     * @param ConfigIdInterface $configId
     * @throws RuntimeException
     * @throws LogicException
     * @return ConfigInterface
     */
    public function getConfig(ConfigIdInterface $configId)
    {
        if ($this->localCache->containsKey($configId->getId())) {
            return $this->localCache->get($configId->getId());
        }

        if (!$this->modelManager->checkDatabase()) {
            throw new LogicException(
                'Database is not synced, if you use ConfigManager, when a db schema may be hasn\'t synced.'
                . ' check it by ConfigManager::modelManager::checkDatabase'
            );
        }

        if (!$this->isConfigurable($configId->getClassName())) {
            throw new RuntimeException(sprintf('Entity "%s" is not configurable', $configId->getClassName()));
        }

        $resultConfig = null !== $this->cache
            ? $this->cache->loadConfigFromCache($configId)
            : null;

        if (!$resultConfig) {
            $model = $this->modelManager->getModelByConfigId($configId);

            $config = new Config($configId);
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

            $models[] = $model = $this->modelManager->getModelByConfigId($config->getId());

            //TODO::refactoring
            $serializableValues = $this->getProvider($config->getId()->getScope())
                ->getPropertyConfig()
                ->getSerializableValues($config->getId());
            $model->fromArray($config->getId()->getScope(), $config->getValues(), $serializableValues);

            if ($this->cache) {
                $this->cache->removeConfigFromCache($config->getId());
            }
        }

        $this->auditManager->log();

        foreach ($models as $model) {
            $this->getEntityManager()->persist($model);
        }

        $this->getEntityManager()->flush();

        $this->persistConfigs   = new \SplObjectStorage();
        $this->originalConfigs  = new ArrayCollection;
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
            $originConfigValue = $originConfig->getValues();
        }

        foreach ($config->getValues() as $key => $value) {
            if (!isset($originConfigValue[$key])) {
                $originConfigValue[$key] = null;
            }
        }

        $diffNew = array_udiff_assoc(
            $config->getValues(),
            $originConfigValue,
            function ($a, $b) {
                return ($a == $b) ? 0 : 1;
            }
        );

        $diffOld = array_udiff_assoc(
            $originConfigValue,
            $config->getValues(),
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
        $result = iterator_to_array($this->persistConfigs, false);

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
     * TODO:: check class name for custom entity
     * @param string $className
     * @param string $mode
     * @return EntityConfigModel
     */
    public function createConfigEntityModel($className, $mode = ConfigModelManager::MODE_DEFAULT)
    {
        if (!$entityModel = $this->modelManager->findModel($className)) {
            $entityModel = $this->modelManager->createEntityModel($className, $mode);

            foreach ($this->getProviders() as $provider) {
                $defaultValues = $provider->getPropertyConfig()->getDefaultValues();

                $metadata = $this->getEntityMetadata($className);
                if ($metadata && isset($metadata->defaultValues[$provider->getScope()])) {
                    $defaultValues = array_merge($defaultValues, $metadata->defaultValues[$provider->getScope()]);
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
                $defaultValues = $provider->getPropertyConfig()->getDefaultValues(PropertyConfigContainer::TYPE_FIELD);

                $metadata = $this->getFieldMetadata($className, $fieldName);
                if ($metadata && isset($metadata->defaultValues[$provider->getScope()])) {
                    $defaultValues = array_merge($defaultValues, $metadata->defaultValues[$provider->getScope()]);
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
     * @param ConfigInterface $config
     * @return ConfigInterface
     */
    private function doMerge(ConfigInterface $config)
    {
        foreach ($this->persistConfigs as $persistConfig) {
            if ($config->getId()->toString() == $persistConfig->getId()->toString()) {
                $config = array_merge($persistConfig->getValues(), $config->getValues());

                break;
            }
        }

        return $config;
    }
}
