<?php

namespace Oro\Bundle\FormBundle\Config;

class FormConfig implements FormConfigInterface
{
    /**
     * @var BlockConfig[]
     */
    protected $blocks = array();

    /**
     * @param BlockConfig $block
     * @return $this
     */
    public function addBlock(BlockConfig $block)
    {
        $this->blocks[$block->getCode()] = $block;

        $this->sortBlocks();

        return $this;
    }

    /**
     * @param $code
     * @return BlockConfig
     */
    public function getBlock($code)
    {
        return $this->blocks[$code];
    }

    /**
     * @param $code
     * @return bool
     */
    public function hasBlock($code)
    {
        return isset($this->blocks[$code]);
    }

    /**
     * @return array|BlockConfig
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @param $blocks
     * @return $this
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;

        $this->sortBlocks();

        return $this;
    }

    /**
     * @param $blockCode
     * @param $subBlockIndex
     * @return SubBlockConfig
     */
    public function getSubBlocks($blockCode, $subBlockIndex)
    {
        return $this->getBlock($blockCode)->getSubBlock($subBlockIndex);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function (BlockConfig $block) {
            return $block->toArray();
        }, $this->blocks);
    }

    protected function sortBlocks()
    {
        $priority = array();
        foreach ($this->blocks as $key => $block) {
            $priority[$key] = $block->getPriority();
        }

        array_multisort($priority, SORT_DESC, $this->blocks);
    }
}
