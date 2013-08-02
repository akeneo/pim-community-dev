<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

class NewConfigModelEvent extends Event
{
    /**
     * @var string
     */
    protected $configId;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigIdInterface $configId
     * @param ConfigManager     $configManager
     */
    public function __construct(ConfigIdInterface $configId, ConfigManager $configManager)
    {
        $this->configId      = $configId;
        $this->configManager = $configManager;
    }

    /**
     * @return ConfigIdInterface
     */
    public function getConfigId()
    {
        return $this->configId;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }
}
