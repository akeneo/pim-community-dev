<?php

namespace Oro\Bundle\EntityConfigBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

class NewFieldEvent extends Event
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param string        $className
     * @param string        $fieldName
     * @param string        $fieldType
     * @param ConfigManager $configManager
     */
    public function __construct($className, $fieldName, $fieldType, ConfigManager $configManager)
    {
        $this->className    = $className;
        $this->fieldName     = $fieldName;
        $this->fieldType     = $fieldType;
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
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }
}
