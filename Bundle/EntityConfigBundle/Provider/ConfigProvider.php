<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigBackendManager;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigBackendManager
     */
    protected $configManager;

    /**
     * @var array|EntityConfig[]
     */
    protected $configs = array();

    /**
     * @param ConfigBackendManager $configManager
     */
    public function __construct(ConfigBackendManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param $className
     * @param $scope
     * @return null|EntityConfig
     */
    public function getConfig($className, $scope)
    {
        if (isset($this->configs[$className])) {
            return $this->configs[$className];
        } else {
            if ($config = $this->configManager->getConfig($className)) {
                return $this->configs[$className] = $config->cloneFilteredByScope($scope);
            }

            return null;
        }
    }
}
