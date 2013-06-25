<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\FormBundle\Config\SubBlockConfig;
use Oro\Bundle\FormBundle\Config\BlockConfig;
use Oro\Bundle\FormBundle\Config\FormConfig;

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

    protected $accessor;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->accessor = $accessor = PropertyAccess::createPropertyAccessor();
        $this->config   = $config;
        $this->scope    = $config['scope'];
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
        if (isset($this->config['entity']) && isset($this->config['entity']['items'])) {
            return $this->config['entity']['items'];
        }

        return array();
    }

    /**
     * @param FormConfig $formConfig
     * @return array
     */
    public function getEntityFormConfig(FormConfig $formConfig = null)
    {
        if (isset($this->config['entity']) && isset($this->config['entity']['form'])) {
            $fromConfig = $formConfig ? $formConfig : new FormConfig;

            foreach ((array)$this->accessor->getValue($this->config['entity']['form'], '[blocks]') as $key => $block) {
                $formBlockConfig = new BlockConfig($this->scope . $key);
                $formBlockConfig->setTitle($this->accessor->getValue($block, '[title]'));
                $formBlockConfig->setClass($this->accessor->getValue($block, '[class]'));

                foreach ((array)$this->accessor->getValue($block, '[subblocks]') as $subBlock) {
                    $formSubBlockConfig = new SubBlockConfig;
                    $formSubBlockConfig->setTitle($this->accessor->getValue($subBlock, '[title]'));

                    $formBlockConfig->addSubBlock($formSubBlockConfig);
                }

                $fromConfig->addBlock($formBlockConfig);
            }

            return $fromConfig;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getEntityGridActions()
    {
        if (isset($this->config['entity']) && isset($this->config['entity']['grid_action'])) {
            return $this->config['entity']['grid_action'];
        }

        return array();
    }

    /**
     * @return array
     */
    public function getEntityLayoutActions()
    {
        if (isset($this->config['entity']) && isset($this->config['entity']['layout_action'])) {
            return $this->config['entity']['layout_action'];
        }

        return array();
    }

    /**
     * @return array
     */
    public function getFieldItems()
    {
        if (isset($this->config['field']) && isset($this->config['field']['items'])) {
            return $this->config['field']['items'];
        }

        return array();
    }

    /**
     * @return array
     */
    public function getFieldFormConfig()
    {
        if (isset($this->config['field']) && isset($this->config['field']['form'])) {
            return $this->config['field']['form'];
        }

        return array();
    }

    /**
     * @return array
     */
    public function getFieldGridActions()
    {
        if (isset($this->config['field']) && isset($this->config['field']['grid_action'])) {
            return $this->config['field']['grid_action'];
        }

        return array();
    }

    /**
     * @return array
     */
    public function getFieldLayoutActions()
    {
        if (isset($this->config['field']) && isset($this->config['field']['layout_action'])) {
            return $this->config['entity']['layout_action'];
        }

        return array();
    }
}
