<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var EntityConfig[]
     */
    protected $entityConfigCache = array();

    /**
     * @var EntityConfigContainer
     */
    protected $configContainer;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @param ConfigManager         $configManager
     * @param EntityConfigContainer $configContainer
     */
    public function __construct(ConfigManager $configManager, EntityConfigContainer $configContainer)
    {
        $this->configManager   = $configManager;
        $this->configContainer = $configContainer;
        $this->scope           = $configContainer->getScope();
    }

    /**
     * @return EntityConfigContainer
     */
    public function getConfigContainer()
    {
        return $this->configContainer;
    }

    /**
     * @param $className
     * @return EntityConfig
     */
    public function getConfig($className)
    {
        $className = $this->getClassName($className);

        if (isset($this->entityConfigCache[$className])) {
            return $this->entityConfigCache[$className];
        } else {
            return $this->entityConfigCache[$className] = $this->configManager->getConfig($className, $this->scope);
        }
    }

    /**
     * @param $className
     * @return bool
     */
    public function hasConfig($className)
    {
        $className = $this->getClassName($className);

        return isset($this->entityConfigCache[$className]) ? true : $this->configManager->hasConfig($className);
    }

    /**
     * @param $className
     * @param $code
     * @return FieldConfig
     */
    public function getFieldConfig($className, $code)
    {
        return $this->getConfig($className)->getField($code);
    }

    /**
     * @param $className
     * @param $code
     * @return FieldConfig
     */
    public function hasFieldConfig($className, $code)
    {
        return $this->hasConfig($className)
            ? $this->getConfig($className)->hasField($code)
            : false;
    }

    /**
     * @param               $className
     * @param  array        $values
     * @param  bool         $flush
     * @return EntityConfig
     */
    public function createEntityConfig($className, array $values, $flush = false)
    {
        $className    = $this->getClassName($className);
        $entityConfig = new EntityConfig($className, $this->scope);

        foreach ($values as $key => $value) {
            $entityConfig->set($key, $value);
        }

        $this->configManager->persist($entityConfig);

        if ($flush) {
            $this->configManager->flush();
        }
    }

    /**
     * @param              $className
     * @param              $code
     * @param              $type
     * @param  array       $values
     * @param  bool        $flush
     * @return FieldConfig
     */
    public function createFieldConfig($className, $code, $type, array $values = array(), $flush = false)
    {
        $className   = $this->getClassName($className);
        $fieldConfig = new FieldConfig($className, $code, $type, $this->scope);

        foreach ($values as $key => $value) {
            $fieldConfig->set($key, $value);
        }

        $this->configManager->persist($fieldConfig);

        if ($flush) {
            $this->configManager->flush();
        }
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

    /**
     * @param ConfigInterface $config
     */
    public function persist(ConfigInterface $config)
    {
        $this->configManager->persist($config);
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
