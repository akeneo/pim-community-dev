<?php

namespace Oro\Bundle\EntityExtendBundle\Config;

use Oro\Bundle\EntityConfigBundle\Provider\AbstractAdvancedConfigProvider;

class ExtendConfigProvider extends AbstractAdvancedConfigProvider
{
    public function isExtend($entityName)
    {
        if ($config = $this->config($entityName)) {
            return $this->get($config, 'is_extend');
        }

        return false;
    }

    public function getExtendClass($entityName)
    {
        if ($config = $this->config($entityName)) {
            return $this->get($config, 'extend_class', true);
        }

        return null;
    }

    public function getProxyClass($entityName)
    {
        if ($config = $this->config($entityName)) {
            return $this->get($config, 'proxy_class', true);
        }

        return null;
    }

    public function getScope()
    {
        return 'extend';
    }
}