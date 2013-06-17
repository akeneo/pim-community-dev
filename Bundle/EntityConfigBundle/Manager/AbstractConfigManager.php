<?php

namespace Oro\Bundle\EntityConfigBundle\Manager;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\ValueConfig;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderInterface;

use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

abstract class AbstractConfigManager implements ConfigManagerInterface
{
    /**
     * @var array|EntityConfig[]
     */
    protected $configs = array();

    /**
     * @var ConfigProviderInterface
     */
    protected $provider;

    /**
     * @param  ConfigInterface  $config
     * @param                   $code
     * @return string
     * @throws RuntimeException
     */
    public function get(ConfigInterface $config, $code)
    {
        /** @var ValueConfig[] $values */
        $values = $config->getValues(function (ValueConfig $value) use ($code) {
            return $value->getCode() == $code;
        });

        if (!count($values)) {
            throw new RuntimeException(sprintf("ValueConfig with code %s in scope %s is not found ", $code, $this->getScope()));
        }

        return reset($values)->getValue();
    }

    /**
     * @param  ConfigInterface $config
     * @param                  $code
     * @return bool
     */
    public function has(ConfigInterface $config, $code)
    {
        $values = $config->getValues(function (ValueConfig $value) use ($code) {
            return $value->getCode() == $code;
        });

        return (bool) count($values);
    }

    /**
     * @param $entity
     * @return EntityConfig
     */
    public function getConfig($entity)
    {
        $className = $this->getClassName($entity);
        if (isset($this->configs[$className])) {
            return $this->configs[$className];
        } else {
            if ($config = $this->provider->getConfig($this->getClassName($entity), $this->getScope())) {
                return $this->configs[$className] = $config;
            }

            return null;
        }
    }

    /**
     * @param ConfigProviderInterface $provider
     */
    public function setConfigProvider(ConfigProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return ConfigProviderInterface
     */
    public function getConfigProvider()
    {
        return $this->provider;
    }

    /**
     * @param $entity
     * @return string
     * @throws RuntimeException
     */
    protected function getClassName($entity)
    {
        $className = $entity;

        if ($entity instanceof PersistentCollection) {
            $className = $entity->getTypeClass()->getName();
        } elseif (is_object($entity)) {
            $className = get_class($entity);
        } elseif (is_array($entity) && count($entity) && is_object($entity[0])) {
            $className = get_class($entity[0]);
        }

        if (!is_string($className)) {
            throw new RuntimeException('AbstractConfigManager::getClassName may take Object, PersistentCollection array of entities or string');
        }

        return $className;
    }
}
