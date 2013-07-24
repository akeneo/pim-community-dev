<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection;

class EntityConfigContainer
{
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
     * @return array
     */
    public function getEntityItems()
    {
        $entityItems = array();
        if (isset($this->config['entity']) && isset($this->config['entity']['items'])) {
            $entityItems = $this->config['entity']['items'];
        }

        return $entityItems;
    }

    /**
     * @return array
     */
    public function getEntityDefaultValues()
    {
        $result = array();
        foreach ($this->getEntityItems() as $code => $item) {
            if (isset($item['default_value'])) {
                $result[$code] = $item['default_value'];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getEntitySerializableValues()
    {
        $result = array();
        foreach ($this->getEntityItems() as $code => $item) {
            if (isset($item['serializable'])) {
                $result[$code] = (bool) $item['serializable'];
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasEntityForm()
    {
        return (boolean) array_filter($this->getEntityItems(), function ($item) {
            return (isset($item['form']) && isset($item['form']['type']));
        });
    }

    /**
     * @return array
     */
    public function getEntityFormBlockConfig()
    {
        $entityFormBlockConfig = null;
        if (isset($this->config['entity'])
            && isset($this->config['entity']['form'])
            && isset($this->config['entity']['form']['block_config'])
        ) {
            $entityFormBlockConfig = $this->config['entity']['form']['block_config'];
        }

        return $entityFormBlockConfig;
    }

    /**
     * @return array
     */
    public function getEntityGridActions()
    {
        $entityGridActions = array();
        if (isset($this->config['entity']) && isset($this->config['entity']['grid_action'])) {
            $entityGridActions = $this->config['entity']['grid_action'];
        }

        return $entityGridActions;
    }

    /**
     * @return array
     */
    public function getEntityLayoutActions()
    {
        $entityLayoutActions = array();
        if (isset($this->config['entity']) && isset($this->config['entity']['layout_action'])) {
            $entityLayoutActions = $this->config['entity']['layout_action'];
        }

        return $entityLayoutActions;
    }

    /**
     * @param  bool  $checkEntityGrid
     * @return array
     */
    public function getFieldItems($checkEntityGrid = false)
    {
        $fieldItems = array();
        if (isset($this->config['field']) && isset($this->config['field']['items'])) {
            if ($checkEntityGrid) {
                $fieldItems = array_filter($this->config['field']['items'], function ($item) {
                    return isset($item['entity_grid']) ? (bool) $item['entity_grid'] : true;
                });
            } else {
                $fieldItems = $this->config['field']['items'];
            }
        }

        return $fieldItems;
    }

    /**
     * @return array
     */
    public function getFieldDefaultValues()
    {
        $result = array();
        foreach ($this->getFieldItems() as $code => $item) {
            if (isset($item['default_value'])) {
                $result[$code] = $item['default_value'];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFieldSerializableValues()
    {
        $result = array();
        foreach ($this->getEntityItems() as $code => $item) {
            if (isset($item['serializable'])) {
                $result[$code] = (bool) $item['serializable'];
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasFieldForm()
    {
        return (boolean) array_filter($this->getFieldItems(), function ($item) {
            return (isset($item['form']) && isset($item['form']['type']));
        });
    }

    /**
     * @return array
     */
    public function getFieldFormConfig()
    {
        $fieldFormConfig = array();
        if (isset($this->config['field']) && isset($this->config['field']['form'])) {
            $fieldFormConfig = $this->config['field']['form'];
        }

        return $fieldFormConfig;
    }

    /**
     * @return array
     */
    public function getFieldGridActions()
    {
        $fieldGridActions = array();
        if (isset($this->config['field']) && isset($this->config['field']['grid_action'])) {
            $fieldGridActions = $this->config['field']['grid_action'];
        }

        return $fieldGridActions;
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
