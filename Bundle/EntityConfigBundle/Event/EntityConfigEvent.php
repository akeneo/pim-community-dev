<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigBackendManager;

class EntityConfigEvent extends Event
{
    /**
     * @var EntityConfig
     */
    protected $entityConfig;

    /**
     * @var ConfigBackendManager
     */
    protected $configManager;

    /**
     * @param EntityConfig         $entityConfig
     * @param ConfigBackendManager $configManager
     */
    public function __construct(EntityConfig $entityConfig, ConfigBackendManager $configManager)
    {
        $this->entityConfig  = $entityConfig;
        $this->configManager = $configManager;
    }

    /**
     * @return EntityConfig
     */
    public function getEntityConfig()
    {
        return $this->entityConfig;
    }

    /**
     * @return ConfigBackendManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

}
