<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var PropertyConfigContainer
     */
    protected $propertyConfigContainer;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @param ConfigManager       $configManager
     * @param ContainerInterface  $container
     * @param                     $scope
     * @param array               $config
     */
    public function __construct(ConfigManager $configManager, ContainerInterface $container, $scope, array $config)
    {
        $this->scope                   = $scope;
        $this->configManager           = $configManager;
        $this->propertyConfigContainer = new PropertyConfigContainer($config, $container);
    }

    /**
     * @return PropertyConfigContainer
     */
    public function getPropertyConfig()
    {
        return $this->propertyConfigContainer;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

    /**
     * @param $className
     * @return bool
     */
    public function isConfigurable($className)
    {
        return $this->configManager->isConfigurable($this->getClassName($className));
    }

    /**
     * @param      $className
     * @param null $fieldName
     * @param null $fieldType
     * @return ConfigIdInterface
     */
    public function getConfigId($className, $fieldName = null, $fieldType = null)
    {
        return $fieldName
            ? new FieldConfigId($this->getClassName($className), $this->getScope(), $fieldName, $fieldType)
            : new EntityConfigId($this->getClassName($className), $this->getScope());
    }

    /**
     * @param      $className
     * @param null $fieldName
     * @return bool
     */
    public function hasConfig($className, $fieldName = null)
    {
        return $this->configManager->hasConfig($this->getConfigId($className, $fieldName));
    }

    /**
     * @param      $className
     * @param null $fieldName
     * @return ConfigInterface
     */
    public function getConfig($className, $fieldName = null)
    {
        return $this->configManager->getConfig($this->getConfigId($className, $fieldName));
    }

    /**
     * @param ConfigIdInterface $configId
     * @return ConfigInterface
     */
    public function getConfigById(ConfigIdInterface $configId)
    {
        return $this->configManager->getConfig($configId);
    }

    /**
     * @param  ConfigIdInterface $configId
     * @param  array             $values
     * @return Config
     */
    public function createConfig(ConfigIdInterface $configId, array $values)
    {
        $config = new Config($configId);
        $type   = $configId instanceof FieldConfigIdInterface
            ? PropertyConfigContainer::TYPE_FIELD
            : PropertyConfigContainer::TYPE_ENTITY;
        $values = array_merge($this->getPropertyConfig()->getDefaultValues($type), $values);

        foreach ($values as $key => $value) {
            $config->set($key, $value);
        }

        $this->merge($config);

        return $config;
    }

    /**
     * @param null $className
     * @return array|ConfigIdInterface[]
     */
    public function getConfigIds($className = null)
    {
        return $this->configManager->getConfigIds($this->getScope(), $className);
    }

    /**
     * @param null $className
     * @return array|ConfigInterface[]
     */
    public function getConfigs($className = null)
    {
        $result = array();

        foreach ($this->getConfigIds($className) as $configId) {
            $result[] = $this->getConfigById($configId);
        }

        return $result;
    }

    /**
     * @param callable $map
     * @param null     $className
     * @return array|ConfigInterface[]
     */
    public function map(\Closure $map, $className = null)
    {
        return array_map($map, $this->getConfigs($className));
    }

    /**
     * @param callable $filter
     * @param null     $className
     * @return array|ConfigInterface[]
     */
    public function filter(\Closure $filter, $className = null)
    {
        return array_filter($this->getConfigs($className), $filter);
    }

    /**
     * @param $entity
     * @return string
     * @throws RuntimeException
     */
    public function getClassName($entity)
    {
        $className = $entity;

        if ($entity instanceof PersistentCollection) {
            $className = $entity->getTypeClass()->getName();
        } elseif (is_object($entity)) {
            $className = get_class($entity);
        } elseif (is_array($entity) && count($entity) && is_object(reset($entity))) {
            $className = get_class(reset($entity));
        }

        if (!is_string($className)) {
            throw new RuntimeException(
                'ConfigProvider::getClassName expects Object, PersistentCollection array of entities or string'
            );
        }

        return $className;
    }

    /**
     * @param      $className
     * @param null $fieldName
     */
    public function clearCache($className, $fieldName = null)
    {
        $this->configManager->clearCache($this->getConfigId($className, $fieldName));
    }

    /**
     * @param ConfigInterface $config
     */
    public function persist(ConfigInterface $config)
    {
        $this->configManager->persist($config);
    }

    /**
     * @param ConfigInterface $config
     */
    public function merge(ConfigInterface $config)
    {
        $this->configManager->merge($config);
    }

    /**
     * Flush configs
     */
    public function flush()
    {
        $this->configManager->flush();
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }
}
