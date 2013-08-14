<?php

namespace Oro\Bundle\EntityConfigBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;

class PropertyConfigContainer
{
    /**
     * Type Of Config
     */
    const TYPE_ENTITY = 'entity';
    const TYPE_FIELD  = 'field';

    /**
     * @var array
     */
    protected $config;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
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
     * @param null   $fieldType
     * @return bool
     */
    public function hasForm($type = self::TYPE_ENTITY, $fieldType = null)
    {
        $type = $this->getConfigType($type);

        return (boolean) $this->getFormItems($type, $fieldType);
    }

    /**
     * @param string $type
     * @param null   $fieldType
     * @return bool
     */
    public function getFormItems($type = self::TYPE_ENTITY, $fieldType = null)
    {
        $type = $this->getConfigType($type);

        return array_filter(
            $this->getItems($type),
            function ($item) use ($fieldType) {
                if (!isset($item['form']) || !isset($item['form']['type'])) {
                    return false;
                }

                if ($fieldType
                    && isset($item['options']['allowed_type'])
                    && !in_array($fieldType, $item['options']['allowed_type'])
                ) {
                    return false;
                }

                return true;
            }
        );
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
            return $type instanceof FieldConfigIdInterface
                ? PropertyConfigContainer::TYPE_FIELD
                : PropertyConfigContainer::TYPE_ENTITY;
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
