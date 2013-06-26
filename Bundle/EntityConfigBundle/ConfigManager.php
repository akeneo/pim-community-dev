<?php

namespace Oro\Bundle\EntityConfigBundle;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;

use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactory;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfigInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy;

use Oro\Bundle\EntityConfigBundle\Cache\CacheInterface;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Event\FieldConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\EntityConfigEvent;
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
     * @var CacheInterface
     */
    protected $configCache;

    /**
     * @var ConfigInterface[]
     */
    protected $persistConfigs = array();

    /**
     * @var ConfigInterface[]
     */
    protected $removeConfigs = array();

    /**
     * @var ConfigProvider[]
     */
    protected $providers = array();

    /**
     * @param MetadataFactory $metadataFactory
     * @param EventDispatcher $eventDispatcher
     * @param ServiceProxy    $proxyEm
     */
    public function __construct(MetadataFactory $metadataFactory, EventDispatcher $eventDispatcher, ServiceProxy $proxyEm)
    {
        $this->metadataFactory = $metadataFactory;
        $this->proxyEm         = $proxyEm;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->configCache = $cache;
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->proxyEm->getService();
    }

    /**
     * @return ConfigProvider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param ConfigProvider $provider
     */
    public function setProvider(ConfigProvider $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @param $className
     * @param $scope
     * @throws Exception\RuntimeException
     * @return EntityConfig
     */
    public function getConfig($className, $scope)
    {
        /** @var ClassHierarchyMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        if (!$metadata->getOutsideClassMetadata()->configurable) {
            throw new RuntimeException(sprintf("Entity '%s' is not Configurable", $className));
        }

        if (null !== $this->configCache
            && $config = $this->configCache->loadConfigFromCache($className, $scope)
        ) {
            return $config;
        } else {
            $entityConfigRepo = $this->em()->getRepository(ConfigEntity::ENTITY_NAME);
            /** @var ConfigEntity $entity */
            $entity = $entityConfigRepo->findOneBy(array('className' => $className));
            if ($entity) {
                $config = new EntityConfig($className, $scope);
                $config->setValues($entity->toArray($scope));

                foreach ($entity->getFields() as $field) {
                    $fieldConfig = new FieldConfig($className, $field->getCode(), $field->getType(), $scope);
                    $fieldConfig->setValues($field->toArray($scope));
                    $config->addField($fieldConfig);
                }

                if (null !== $this->configCache) {
                    $this->configCache->putConfigInCache($config);
                }

                return $config;
            } else {
                return new EntityConfig($className, $scope);
            }
        }
    }

    public function hasConfig($className)
    {
        /** @var ClassHierarchyMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($className);

        return $metadata->getOutsideClassMetadata()->configurable;
    }

    public function clearCache()
    {
        $this->configCache->clearCache();
    }
    public function updateAll()
    {
        /** @var $doctrineMetadata ClassMetadata */
        $entities = array();
        foreach ($this->em()->getMetadataFactory()->getAllMetadata() as $doctrineMetadata) {
            /** @var ClassHierarchyMetadata $metadata */
            $metadata = $this->metadataFactory->getMetadataForClass($doctrineMetadata->getName());
            if ($metadata->getOutsideClassMetadata()->configurable
                && !$this->em()->getRepository(ConfigEntity::ENTITY_NAME)->findOneBy(array(
                    'className' => $doctrineMetadata->getName()))
            ) {
                // listeners can add their configs for new ConfigEntity
                $entity = new ConfigEntity($doctrineMetadata->getName());

                $this->eventDispatcher->dispatch(
                    Events::newEntityConfig,
                    new EntityConfigEvent($doctrineMetadata->getName(), $this)
                );

                foreach ($doctrineMetadata->getFieldNames() as $fieldName) {
                    $type = $doctrineMetadata->getTypeOfField($fieldName);
                    $entity->addFiled(new ConfigField($fieldName, $type));
                    $this->eventDispatcher->dispatch(
                        Events::newFieldConfig,
                        new FieldConfigEvent($doctrineMetadata->getName(), $fieldName, $type, $this)
                    );
                }

                foreach ($doctrineMetadata->getAssociationNames() as $fieldName) {
                    $type = $doctrineMetadata->isSingleValuedAssociation($fieldName) ? 'ref-one' : 'ref-many';
                    $entity->addFiled(new ConfigField($fieldName, $type));
                    $this->eventDispatcher->dispatch(
                        Events::newFieldConfig,
                        new FieldConfigEvent($doctrineMetadata->getName(), $fieldName, $type, $this)
                    );
                }

                $entities[$entity->getClassName()] = $entity;
            }
        }

        $this->flush($entities);
    }

    /**
     * @param ConfigInterface $config
     */
    public function persist(ConfigInterface $config)
    {
        $this->persistConfigs[spl_object_hash($config)] = $config;

        if ($config instanceof EntityConfigInterface) {
            foreach($config->getFields() as $fieldConfig){
                $this->persistConfigs[spl_object_hash($fieldConfig)] = $fieldConfig;
            }
        }
    }

    /**
     * @param ConfigInterface $config
     */
    public function remove(ConfigInterface $config)
    {
        $this->removeConfigs[spl_object_hash($config)] = $config;

        if ($config instanceof EntityConfigInterface) {
            foreach($config->getFields() as $fieldConfig){
                $this->removeConfigs[spl_object_hash($fieldConfig)] = $fieldConfig;
            }
        }
    }

    /**
     * @param array $entities
     */
    public function flush(array $entities = array())
    {
        foreach ($this->persistConfigs as $config) {
            $className = $config->getClassName();

            if (isset($entities[$className])) {
                $configEntity = $entities[$className];
            } else {
                $configEntity = $entities[$className] = $this->findOrCreateConfigEntity($className);
            }

            if ($config instanceof FieldConfigInterface) {
                if (!$configField = $configEntity->getField($config->getCode())) {
                    $configField = new ConfigField($config->getCode(), $config->getType());
                    $configEntity->addFiled($configField);
                }

                $configField->fromArray($config->getScope(), $config->getValues());
            } else {
                $configEntity->fromArray($config->getScope(), $config->getValues());
            }

            $this->configCache->removeConfigFromCache($className, $config->getScope());
        }

        // TODO:: remove configs

        foreach ($entities as $entity) {
            $this->em()->persist($entity);
        }

        $this->em()->flush();
    }

    /**
     * @param $className
     * @return ConfigEntity
     */
    protected function findOrCreateConfigEntity($className)
    {
        $entityConfigRepo = $this->em()->getRepository(ConfigEntity::ENTITY_NAME);
        /** @var ConfigEntity $entity */
        $entity = $entityConfigRepo->findOneBy(array('className' => $className));
        if (!$entity) {
            $entity = new ConfigEntity($className);
        }

        return $entity;
    }
}
