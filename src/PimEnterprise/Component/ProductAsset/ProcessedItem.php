<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

/**
 * Represent an item that has been processed. The idea is to know what happens and for which reason.
 * Example:
 *      items  | state   | reason
 *      -------|---------|-----------------------------------------------
 *      item 1 | success |
 *      item 2 | error   | Impossible to copy the file XXX
 *      item 3 | skipped | Non relevant when the locale is not activated
 *
 * This class should not be used for large batches.
 *
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
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

    /** @var \Exception */
    protected $exception;

    /**
     * @param mixed      $item
     * @param string     $state
     * @param string     $reason
     * @param \Exception $e
     */
    public function __construct($item, $state, $reason = null, \Exception $e = null)
    {
        $this->item      = $item;
        $this->state     = $state;
        $this->reason    = $reason;
        $this->exception = $e;
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

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }
}
