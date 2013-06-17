<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

use Oro\Bundle\EntityConfigBundle\Config\ValueConfig;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

abstract class AbstractAdvancedConfigProvider extends ConfigProvider implements AdvancedConfigProviderInterface
{
    /**
     * @param $className
     * @return null|EntityConfig
     */
    public function config($className)
    {
        return parent::getConfig($this->getClassName($className), $this->getScope());
    }

    /**
     * @param  ConfigInterface $config
     * @param                  $code
     * @param bool             $strict
     * @throws RuntimeException
     * @return string
     */
    public function get(ConfigInterface $config, $code, $strict = false)
    {
        /** @var ValueConfig[] $values */
        $values = $config->getValues(function (ValueConfig $value) use ($code) {
            return $value->getCode() == $code;
        });

        if (!count($values)) {
            if ($strict) {
                throw new RuntimeException(sprintf("ValueConfig with code %s in scope %s is not found ", $code, $this->getScope()));
            } else {
                return null;
            }
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

        return (bool)count($values);
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
            throw new RuntimeException('AbstractAdvancedConfigProvider::getClassName expects Object, PersistentCollection array of entities or string');
        }

        return $className;
    }
}