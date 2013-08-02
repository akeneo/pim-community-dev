<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

class NewEntityConfigModelEvent extends Event
{
    /**
     * @var EntityConfigModel
     */
    protected $configModel;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct(EntityConfigModel $configModel, ConfigManager $configManager)
    {
        $this->configModel   = $configModel;
        $this->configManager = $configManager;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->configModel->getClassName();
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }
}
