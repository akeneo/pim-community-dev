<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class NewFieldConfigModelEvent extends Event
{
    /**
     * @var FieldConfigModel
     */
    protected $configModel;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct(FieldConfigModel $configModel, ConfigManager $configManager)
    {
        $this->configModel   = $configModel;
        $this->configManager = $configManager;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->configModel->getEntity()->getClassName();
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->configModel->getFieldName();
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->configModel->getType();
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }
}
