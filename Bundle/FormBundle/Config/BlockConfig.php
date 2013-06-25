<?php

namespace Oro\Bundle\FormBundle\Config;

class BlockConfig implements FormConfigInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var SubBlockConfig
     */
    protected $subBlocks = array();

    /**
     * @param $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param SubBlockConfig $config
     * @return $this
     */
    public function addSubBlock(SubBlockConfig $config)
    {
        $this->subBlocks[] = $config;

        return $this;
    }

    /**
     * @param $subBlocks
     * @return $this
     */
    public function setSubBlocks($subBlocks)
    {
        $this->subBlocks = $subBlocks;

        return $this;
    }

    /**
     * @return array|SubBlockConfig
     */
    public function getSubBlocks()
    {
        return $this->subBlocks;
    }

    /**
     * @param $index
     * @return SubBlockConfig
     */
    public function getSubBlock($index)
    {
        return $this->subBlocks[$index];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'title'     => $this->title,
            'class'     => $this->class,
            'subblocks' => array_map(function (SubBlockConfig $config) {
                return $config->toArray();
            }, $this->subBlocks)
        );
    }
}
