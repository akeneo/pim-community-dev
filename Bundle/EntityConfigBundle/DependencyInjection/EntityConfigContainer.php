<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class EntityConfigContainer
{
    /**
     * Type Of Config
     */
    const TYPE_ENTITY = 'entity';
    const TYPE_FIELD  = 'field';

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param $scope
     * @param $config
     */
    public function __construct($scope, $config)
    {
        $this->config = $config;
        $this->scope  = $scope;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getItems($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $items = array();
        if (isset($this->config[$type]) && isset($this->config[$type]['items'])) {
            $items = $this->config[$type]['items'];
        }

        return $items;
    }

    /**
     * @param string|ConfigIdInterface $type
     * @return array
     */
    public function getDefaultValues($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $result = array();
        foreach ($this->getItems($type) as $code => $item) {
            if (isset($item['options']['default_value'])) {
                $result[$code] = $item['options']['default_value'];
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getInternalValues($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $result = array();
        foreach ($this->getItems($type) as $code => $item) {
            if (isset($item['options']['internal']) && $item['options']['internal']) {
                $result[$code] = 0;
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getSerializableValues($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $result = array();
        foreach ($this->getItems($type) as $code => $item) {
            if (isset($item['options']['serializable'])) {
                $result[$code] = (bool) $item['options']['serializable'];
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasForm($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        return (boolean) array_filter($this->getItems($type), function ($item) {
            return (isset($item['form']) && isset($item['form']['type']));
        });
    }

    /**
     * @param string $type
     * @return array
     */
    public function getFormConfig($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $fieldFormConfig = array();
        if (isset($this->config[$type]) && isset($this->config[$type]['form'])) {
            $fieldFormConfig = $this->config[$type]['form'];
        }

        return $fieldFormConfig;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getFormBlockConfig($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $entityFormBlockConfig = null;
        if (isset($this->config[$type])
            && isset($this->config[$type]['form'])
            && isset($this->config[$type]['form']['block_config'])
        ) {
            $entityFormBlockConfig = $this->config[$type]['form']['block_config'];
        }

        return $entityFormBlockConfig;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getGridActions($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $entityGridActions = array();
        if (isset($this->config[$type]) && isset($this->config[$type]['grid_action'])) {
            $entityGridActions = $this->config[$type]['grid_action'];
        }

        return $entityGridActions;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getLayoutActions($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $entityLayoutActions = array();
        if (isset($this->config[$type]) && isset($this->config[$type]['layout_action'])) {
            $entityLayoutActions = $this->config[$type]['layout_action'];
        }

        return $entityLayoutActions;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getRequiredPropertyValues($type = self::TYPE_ENTITY)
    {
        $type = $this->getConfigType($type);

        $result = array();
        foreach ($this->getItems($type) as $code => $item) {
            if (isset($item['options']['required_property'])) {
                $result[$code] = $item['options']['required_property'];
            }
        }

        return $result;
    }

    /**
     * @param $type
     * @return string
     */
    protected function getConfigType($type)
    {
        if ($type instanceof ConfigIdInterface) {
            return $type instanceof FieldConfigId ? EntityConfigContainer::TYPE_FIELD : EntityConfigContainer::TYPE_ENTITY;
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getFieldLayoutActions()
    {
        $fieldLayoutActions = array();
        if (isset($this->config['field']) && isset($this->config['field']['layout_action'])) {
            $fieldLayoutActions = $this->config['field']['layout_action'];
        }

        return $fieldLayoutActions;
    }
}
