<?php

namespace Oro\Bundle\EntityExtendBundle\Config;

use Oro\Bundle\EntityConfigBundle\Provider\AbstractConfigProvider;

class ExtendConfigProvider extends AbstractConfigProvider
{
    public function isExtend($entityName)
    {
        if (!$this->hasConfig($entityName)) {
            return false;
        }

        return $this->getConfig($entityName)->has('is_extend');
    }

    public function getExtendClass($entityName)
    {
        return $this->getConfig($entityName)->get('extend_class', true);
    }

    public function getProxyClass($entityName)
    {
        return $this->getConfig($entityName)->get('proxy_class', true);
    }

    public function getScope()
    {
        return 'extend';
    }
}