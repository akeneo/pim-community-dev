<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
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
     * @return bool
     */
    public function isConfigurable($className)
    {
        return $this->configManager->isConfigurable($this->getClassName($className));
    }

    /**
     * @param $className
     * @return Config
     */
    public function getConfig($className)
    {
        return $this->configManager->getConfig(new EntityConfigId($this->getClassName($className), $this->scope));
    }

    /**
     * @param $className
     * @param $code
     * @return Config
     */
    public function getFieldConfig($className, $code)
    {
        $configId = new FieldConfigId($className, $this->scope, $code);
        return $this->getConfig($configId);
    }

    /**
     * @param $className
     * @param $code
     * @return Config
     */
    public function hasFieldConfig($className, $code)
    {
        return $this->isConfigurable($className)
            ? $this->getConfig($className)->hasField($code)
            : false;
    }

    /**
     * @param  ConfigIdInterface $configId
     * @param  array             $values
     * @return Config
     */
    public function createConfig(ConfigIdInterface $configId, array $values)
    {
        $entityConfig = new Config($configId);
        $values       = array_merge($this->getConfigContainer()->getEntityDefaultValues(), $values);

        foreach ($values as $key => $value) {
            $entityConfig->set($key, $value);
        }

        $this->persist($entityConfig);
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
