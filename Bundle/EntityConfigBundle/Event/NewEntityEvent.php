<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

class NewEntityEvent extends Event
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param string        $className
     * @param ConfigManager $configManager
     */
    public function __construct($className, ConfigManager $configManager)
    {
        $this->className     = $className;
        $this->configManager = $configManager;
    }

    /**
     * @return EntityConfig
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }
}
