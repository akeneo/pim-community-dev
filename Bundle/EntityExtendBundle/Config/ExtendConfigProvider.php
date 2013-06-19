<?php

namespace Oro\Bundle\EntityExtendBundle\Config;

use Oro\Bundle\EntityConfigBundle\Provider\AbstractConfigProvider;

class ExtendConfigProvider extends AbstractConfigProvider
{
    public function isExtend($entityName)
    {
        if (!$this->hasConfig($this->getClassName($entityName))) {
            return false;
        }

        return $this->getConfig($this->getClassName($entityName))->has('is_extend');
    }

    public function getExtendClass($entityName)
    {
        return $this->getConfig($this->getClassName($entityName))->get('extend_class', true);
    }

    public function getProxyClass($entityName)
    {
        return $this->getConfig($this->getClassName($entityName))->get('proxy_class', true);
    }

    public function getScope()
    {
        return 'extend';
    }
}