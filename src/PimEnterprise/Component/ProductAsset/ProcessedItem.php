<?php

namespace PimEnterprise\Component\ProductAsset;

//TODO
class ProcessedItem
{
    /** @staticvar string */
    const STATE_SUCCESS = 'success';

    /** @staticvar string */
    const STATE_ERROR   = 'error';

    /** @staticvar string */
    const STATE_SKIPPED = 'skipped';

    /** @var mixed */
    protected $item;

    /** @var string */
    protected $state;

    /** @var string */
    protected $reason;

    /**
     * @param mixed  $item
     * @param string $state
     * @param string $reason
     */
    public function __construct($item, $state, $reason = null)
    {
        $this->item   = $item;
        $this->state  = $state;
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
