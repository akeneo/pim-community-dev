<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

abstract class AbstractConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var array|EntityConfig[]
     */
    protected $configs = array();

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param $className
     * @return EntityConfig
     */
    public function getConfig($className)
    {
        $className = $this->getClassName($className);

        if (isset($this->configs[$className])) {
            return $this->configs[$className];
        } else {
            return $this->configs[$className] = $this->configManager->getConfig($className, $this->getScope());
        }
    }

    /**
     * @param $className
     * @return bool
     */
    public function hasConfig($className)
    {
        $className = $this->getClassName($className);

        return isset($this->configs[$className]) ? true : $this->configManager->hasConfig($className);
    }

    /**
     * @param       $className
     * @param array $values
     * @return EntityConfig
     */
    public function createEntityConfig($className, array $values)
    {
        $className = $this->getClassName($className);
        $entityConfig = new EntityConfig($className, $this->getScope());

        foreach ($values as $key => $value) {
            $entityConfig->set($key, $value);
        }

        $this->configManager->persist($entityConfig);
    }

    /**
     * @param       $className
     * @param       $code
     * @param       $type
     * @param array $values
     * @return FieldConfig
     */
    public function createFieldConfig($className, $code, $type, array $values = array())
    {
        $className = $this->getClassName($className);
        $fieldConfig = new FieldConfig($className, $code, $type, $this->getScope());

        foreach ($values as $key => $value) {
            $fieldConfig->set($key, $value);
        }

        $this->configManager->persist($fieldConfig);
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
            throw new RuntimeException('AbstractAdvancedConfigProvider::getClassName expects Object, PersistentCollection array of entities or string');
        }

        return $className;
    }
}
