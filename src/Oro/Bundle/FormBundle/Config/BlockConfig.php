<?php

namespace Oro\Bundle\FormBundle\Config;

class BlockConfig implements FormConfigInterface
{
    /**
     * @var array
     */
    protected $blockConfig;

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
    protected $description;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var SubBlockConfig[]
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
     * @param mixed $blockConfig
     * @return $this
     */
    public function setBlockConfig($blockConfig)
    {
        $this->blockConfig = $blockConfig;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlockConfig()
    {
        return $this->blockConfig;
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
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param SubBlockConfig $config
     * @return $this
     */
    public function addSubBlock(SubBlockConfig $config)
    {
        $this->subBlocks[$config->getCode()] = $config;

        $this->sortSubBlocks();

        return $this;
    }

    /**
     * @param $subBlocks
     * @return $this
     */
    public function setSubBlocks($subBlocks)
    {
        $this->subBlocks = $subBlocks;

        $this->sortSubBlocks();

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
     * @param $code
     * @return SubBlockConfig
     */
    public function getSubBlock($code)
    {
        return $this->subBlocks[$code];
    }

    /**
     * @param $code
     * @return boolean
     */
    public function hasSubBlock($code)
    {
        return isset($this->subBlocks[$code]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'title'       => $this->title,
            'description' => $this->description,
            'class'       => $this->class,
            'subblocks'   => array_map(
                function (SubBlockConfig $config) {
                    return $config->toArray();
                },
                $this->subBlocks
            )
        );
    }

    protected function sortSubBlocks()
    {
        $priority = array();
        foreach ($this->subBlocks as $key => $subBlock) {
            $priority[$key] = $subBlock->getPriority();
        }

        array_multisort($priority, SORT_DESC, $this->subBlocks);
    }
}
