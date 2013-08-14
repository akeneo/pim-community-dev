<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Metadata\MetadataFactory;

use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\EntityConfigBundle\Exception\LogicException;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy;
use Oro\Bundle\EntityConfigBundle\Metadata\ConfigClassMetadata;

use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

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

class ConfigManager
{
    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var ServiceProxy
     */
    protected $proxyEm;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var CacheProvider
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
     * @var ConfigProvider[]|ArrayCollection
     */
    protected $providers;

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
     * @param ServiceProxy    $proxyEm
     * @param ServiceProxy    $security
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        EventDispatcher $eventDispatcher,
        ServiceProxy $proxyEm,
        ServiceProxy $security
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->proxyEm         = $proxyEm;
        $this->eventDispatcher = $eventDispatcher;

        $this->providers        = new ArrayCollection;
        $this->localCache       = new ArrayCollection;
        $this->persistConfigs   = new \SplObjectStorage();
        $this->originalConfigs  = new ArrayCollection;
        $this->configChangeSets = new ArrayCollection;

        $this->modelManager = new ConfigModelManager($proxyEm);
        $this->auditManager = new AuditManager($this, $security);
    }

    /**
     * @param CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
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
     * @return ConfigProvider[]|ArrayCollection
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param ConfigProvider $provider
     * @return $this
     */
    public function addProvider(ConfigProvider $provider)
    {
        $this->providers->set($provider->getScope(), $provider);

        return $this;
    }

    /**
     * @param $scope
     * @return ConfigProvider
     */
    public function getProvider($scope)
    {
        return $this->providers->get($scope);
    }

    /**
     * @param $scope
     * @return bool
     */
    public function hasProvider($scope)
    {
        return $this->providers->containsKey($scope);
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
     * @return ConfigClassMetadata|null
     */
    public function getClassMetadata($className)
    {
        return class_exists($className) ? $this->metadataFactory->getMetadataForClass($className) : null;
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
     * @param $className
     * @return bool
     */
    public function isConfigurable($className)
    {
        return (bool)$this->modelManager->findModel($className);
    }

    /**
     * @param $scope
     * @param $className
     * @return array
     */
    public function getConfigIds($scope, $className = null)
    {
        $entityModels = $this->modelManager->getModels($className);

        return array_map(
            function (AbstractConfigModel $model) use ($scope) {
                if ($model instanceof FieldConfigModel) {
                    return new FieldConfigId($model->getClassName(), $scope, $model->getFieldName(), $model->getType());
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
            && $config = $this->loadConfigFromCache($configId)
        ) {
            return true;
        }

        return (bool)$this->modelManager->getModelByConfigId($configId);
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
            throw new RuntimeException(sprintf('Entity "%s" is not Configurable', $configId->getClassName()));
        }

        $resultConfig = null !== $this->cache
            ? $this->loadConfigFromCache($configId)
            : null;

        if (!$resultConfig) {
            $model = $this->modelManager->getModelByConfigId($configId);

            $config = new Config($configId);
            $config->setValues($model->toArray($configId->getScope()));

            if (null !== $this->cache) {
                $this->putConfigInCache($config);
            }

            $resultConfig = $config;
        }

        //local cache
        $this->localCache->set($resultConfig->getConfigId()->getId(), $resultConfig);

        //for calculate change set
        $this->originalConfigs->set($resultConfig->getConfigId()->getId(), clone $resultConfig);

        return $resultConfig;
    }


    /**
     * @param ConfigIdInterface $configId
     */
    public function clearCache(ConfigIdInterface $configId)
    {
        if ($this->cache) {
            $this->removeConfigFromCache($configId);
        }
    }

    /**
     * Remove All cache
     */
    public function clearCacheAll()
    {
        if ($this->cache) {
            $this->cache->deleteAll();
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

            $models[] = $model = $this->modelManager->getModelByConfigId($config->getConfigId());

            //TODO::refactoring
            $serializableValues = $this->getProvider($config->getConfigId()->getScope())
                ->getPropertyConfig()
                ->getSerializableValues($config->getConfigId());
            $model->fromArray($config->getConfigId()->getScope(), $config->getValues(), $serializableValues);

            if ($this->cache) {
                $this->removeConfigFromCache($config->getConfigId());
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
     */
    public function calculateConfigChangeSet(ConfigInterface $config)
    {
        $originConfigValue = array();
        if ($this->originalConfigs->containsKey($config->getConfigId()->getId())) {
            $originConfig      = $this->originalConfigs->get($config->getConfigId()->getId());
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


        if (!$this->configChangeSets->containsKey($config->getConfigId()->getId())) {
            $this->configChangeSets->set($config->getConfigId()->getId(), array());
        }

        if (count($diff)) {
            $changeSet = array_merge($this->configChangeSets->get($config->getConfigId()->getId()), $diff);
            $this->configChangeSets->set($config->getConfigId()->getId(), $changeSet);
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
        return $this->configChangeSets->containsKey($config->getConfigId()->getId())
            ? $this->configChangeSets->get($config->getConfigId()->getId())
            : array();
    }

    /**
     * @param        $className
     * @param string $mode
     * @return EntityConfigModel
     */
    public function createConfigEntityModel($className, $mode = ConfigModelManager::MODE_DEFAULT)
    {
        if (!$entityModel = $this->modelManager->findModel($className)) {

            $metadata    = $this->getClassMetadata($className);
            $entityModel = $this->modelManager->createEntityModel($className, $mode);

            foreach ($this->getProviders() as $provider) {
                $defaultValues = $provider->getPropertyConfig()->getDefaultValues();
                if ($metadata && isset($metadata->defaultValues[$provider->getScope()])) {
                    $defaultValues = $metadata->defaultValues[$provider->getScope()];
                }

                $entityId = new EntityConfigId($className, $provider->getScope());
                $config   = $provider->createConfig($entityId, $defaultValues);

                $this->localCache->set($config->getConfigId()->getId(), $config);
            }

            $this->eventDispatcher->dispatch(
                Events::NEW_ENTITY_CONFIG_MODEL,
                new NewEntityConfigModelEvent($entityModel, $this)
            );
        }

        return $entityModel;
    }

    /**
     * TODO::implement default value for configs
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
                $fieldId       = new FieldConfigId($className, $provider->getScope(), $fieldName, $fieldType);
                $config        = $provider->createConfig($fieldId, $defaultValues);

                $this->localCache->set($config->getConfigId()->getId(), $config);
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
            if ($config->getConfigId()->getId() == $persistConfig->getConfigId()->getId()) {
                $config = array_merge($persistConfig->getValues(), $config->getValues());

                break;
            }
        }

        return $config;
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool|ConfigInterface
     */
    private function loadConfigFromCache(ConfigIdInterface $configId)
    {
        return unserialize($this->cache->fetch($configId->getId()));
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool
     */
    private function removeConfigFromCache(ConfigIdInterface $configId)
    {
        return $this->cache->delete($configId->getId());
    }

    /**
     * @param ConfigInterface $config
     * @return bool
     */
    private function putConfigInCache(ConfigInterface $config)
    {
        return $this->cache->save($config->getConfigId()->getId(), serialize($config));
    }
}
