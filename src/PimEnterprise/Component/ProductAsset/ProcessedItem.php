<?php

namespace PimEnterprise\Component\ProductAsset;

class ProcessedItem
{
    const STATE_SUCCESS = 'success';
    const STATE_ERROR = 'error';
    const STATE_SKIPPED = 'skipped';

    protected $item;
    protected $state;
    protected $reason;

    /**
     * @param mixed  $item
     * @param string $state
     * @param string $reason
     */
    public function __construct($item, $state, $reason = null)
    {
        $this->item = $item;
        $this->state = $state;
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }
}
