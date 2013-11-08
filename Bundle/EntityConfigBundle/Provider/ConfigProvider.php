<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\PersistentCollection;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

/**
 * The configuration provider can be used to manage configuration data inside particular configuration scope.
 */
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
     * Constructor.
     *
     * @param ConfigManager      $configManager
     * @param ContainerInterface $container
     * @param string             $scope
     * @param array              $config
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
     * Gets an instance of FieldConfigId or EntityConfigId depends on the given parameters.
     *
     * @param string      $className
     * @param string|null $fieldName
     * @param string|null $fieldType
     * @return ConfigIdInterface
     */
    public function getId($className, $fieldName = null, $fieldType = null)
    {
        return $fieldName
            ? new FieldConfigId($this->getClassName($className), $this->getScope(), $fieldName, $fieldType)
            : new EntityConfigId($this->getClassName($className), $this->getScope());
    }

    /**
     * Makes a copy of the given configuration id,
     * but sets the scope property of the new id equal to the scope of this configuration provider.
     *
     * @param ConfigIdInterface $configId
     * @return ConfigIdInterface
     */
    public function copyId(ConfigIdInterface $configId)
    {
        if ($configId instanceof FieldConfigId) {
            return $this->getId($configId->getClassName(), $configId->getFieldName(), $configId->getFieldType());
        } else {
            return $this->getId($configId->getClassName());
        }
    }

    /**
     * Determines if this provider has configuration data for the given class or field.
     *
     * @param string      $className
     * @param string|null $fieldName
     * @return bool
     */
    public function hasConfig($className, $fieldName = null)
    {
        return $this->configManager->hasConfig($this->getClassName($className), $fieldName);
    }

    /**
     * @param ConfigIdInterface $configId
     * @return bool
     */
    public function hasConfigById(ConfigIdInterface $configId)
    {
        if ($configId instanceof FieldConfigId) {
            return $this->configManager->hasConfig($configId->getClassName(), $configId->getFieldName());
        } else {
            return $this->configManager->hasConfig($configId->getClassName());
        }
    }

    /**
     * Gets configuration data for the given class or field.
     *
     * @param string      $className
     * @param string|null $fieldName
     * @return ConfigInterface
     */
    public function getConfig($className, $fieldName = null)
    {
        return $this->configManager->getConfig($this->getId($className, $fieldName));
    }

    /**
     * Gets configuration data for an object (class or field) which is represented by the given id.
     *
     * @param ConfigIdInterface $configId
     * @return ConfigInterface
     */
    public function getConfigById(ConfigIdInterface $configId)
    {
        return $this->configManager->getConfig($this->copyId($configId));
    }

    /**
     * Creates an instance if Config class which stores configuration data for an object
     * which is represented by the given id.
     * The returned object is initialized with data specified $values argument.
     *
     * @param  ConfigIdInterface $configId
     * @param  array             $values An associative array contains configuration properties
     *                                   key = property name
     *                                   value = property value
     * @return Config
     */
    public function createConfig(ConfigIdInterface $configId, array $values)
    {
        $config = new Config($configId);
        if ($configId instanceof FieldConfigId) {
            $type          = PropertyConfigContainer::TYPE_FIELD;
            $defaultValues = $this->getPropertyConfig()->getDefaultValues($type, $configId->getFieldType());
        } else {
            $type          = PropertyConfigContainer::TYPE_ENTITY;
            $defaultValues = $this->getPropertyConfig()->getDefaultValues($type);
        }

        $values = array_merge($defaultValues, $values);

        foreach ($values as $key => $value) {
            $config->set($key, $value);
        }

        $this->merge($config);

        return $config;
    }

    /**
     * Gets a list of ids for all classes (if $className is not specified) or all fields of
     * the given $className, which can be managed by this provider.
     *
     * @param string|null $className
     * @return array|ConfigIdInterface[]
     */
    public function getIds($className = null)
    {
        if ($className) {
            $className = $this->getClassName($className);
        }

        return $this->configManager->getIds($this->getScope(), $className);
    }

    /**
     * Gets configuration data for all classes (if $className is not specified) or all fields of
     * the given $className.
     *
     * @param string|null $className
     * @return array|ConfigInterface[]
     */
    public function getConfigs($className = null)
    {
        $result = array();

        foreach ($this->getIds($className) as $configId) {
            $result[] = $this->getConfigById($configId);
        }

        return $result;
    }

    /**
     * Applies the callback to configuration data of all classes (if $className is not specified)
     * or all fields of the given $className.
     *
     * @param callable    $callback The callback function to run for configuration data for each object
     * @param string|null $className
     * @return array|ConfigInterface[]
     */
    public function map(\Closure $callback, $className = null)
    {
        return array_map($callback, $this->getConfigs($className));
    }

    /**
     * Filters configuration data of all classes (if $className is not specified)
     * or all fields of the given $className using the given callback function.
     *
     * @param callable    $callback The callback function to use
     * @param string|null $className
     * @return array|ConfigInterface[]
     */
    public function filter(\Closure $callback, $className = null)
    {
        return array_filter($this->getConfigs($className), $callback);
    }

    /**
     * Gets the real fully-qualified class name of the given object (even if its a proxy).
     *
     * @param string|object|array|PersistentCollection $object
     * @return string
     * @throws RuntimeException
     */
    public function getClassName($object)
    {
        if ($object instanceof PersistentCollection) {
            $className = $object->getTypeClass()->getName();
        } elseif (is_string($object)) {
            $className = ClassUtils::getRealClass($object);
        } elseif (is_object($object)) {
            $className = ClassUtils::getClass($object);
        } elseif (is_array($object) && count($object) && is_object(reset($object))) {
            $className = ClassUtils::getClass(reset($object));
        } else {
            $className = $object;
        }

        if (!is_string($className)) {
            throw new RuntimeException(
                sprintf(
                    'ConfigProvider::getClassName expects Object, ' .
                    'PersistentCollection, array of entities or string. "%s" given',
                    gettype($className)
                )
            );
        }

        return $className;
    }

    /**
     * Removes configuration data for the given object (entity or field) from the cache.
     *
     * @param string      $className
     * @param string|null $fieldName
     */
    public function clearCache($className, $fieldName = null)
    {
        $this->configManager->clearCache($this->getId($className, $fieldName));
    }

    /**
     * Tells the ConfigManager to make the given configuration data managed and persistent.
     *
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
     * Flushes all changes to configuration data that have been queued up to now to the database.
     */
    public function flush()
    {
        $this->configManager->flush();
    }

    /**
     * Gets the name of the scope this provider works with.
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }
}
