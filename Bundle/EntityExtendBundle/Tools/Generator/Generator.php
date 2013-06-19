<?php

namespace Oro\Bundle\EntityExtendBundle\Tools\Generator;

use Oro\Bundle\EntityExtendBundle\Config\ExtendConfigProvider;

class Generator
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var ExtendConfigProvider
     */
    protected $configProvider;

    /**
     * @param ExtendConfigProvider $configProvider
     * @param                      $mode
     */
    public function __construct(ExtendConfigProvider $configProvider, $mode)
    {
        $this->mode           = $mode;
        $this->configProvider = $configProvider;
    }

    /**
     * @param $entityName
     */
    public function checkEntityCache($entityName)
    {
        $extendClass = $this->generateExtendClassName($entityName);

        var_dump($extendClass);

        if (!class_exists($extendClass)) {


        }
    }

    public function generateExtendClassName($entityName)
    {
        return 'Extend\\Entity\\' . $this->mode . '\\' . str_replace('\\', '', $entityName);
    }

    public function generateProxyClassName($entityName)
    {
        return 'Extend\\Entity\\Proxy\\' . str_replace('\\', '', $entityName);
    }
}