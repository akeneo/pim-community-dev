<?php

namespace Oro\Bundle\EntityExtendBundle\Tools\Generator;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class Generator
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ConfigProvider $configProvider
     * @param                $mode
     */
    public function __construct(ConfigProvider $configProvider, $mode)
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

        //var_dump($extendClass);
//        print_r(array_keys(
//            $this->configProvider->getConfig($entityName)->getFields()
//        ));
//        var_dump(
//            $this->configProvider->getConfig($entityName)->getValues()
//        );


        if (!class_exists($extendClass)) {
            //die($extendClass);

        }

        //die('generator');
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