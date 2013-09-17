<?php


namespace Oro\Bundle\ImapBundle\Connector;

use Oro\Bundle\ImapBundle\Mail\Storage\Imap;
use Oro\Bundle\ImapBundle\Mail\Storage\Message;

class ImapMessageIterator implements \Iterator
{
    /**
     * @var Imap
     */
    private $imap;

    /**
     * @var int[]
     */
    private $ids;

    /**
     * @var bool
     */
    private $reverse = false;

    /**
     * @var int
     */
    private $iterationMin = null;

    /**
     * @var int
     */
    private $iterationMax = null;

    /**
     * @var int
     */
    private $iterationPos = null;

    /**
     * Constructor
     *
     * @param Imap $imap
     * @param int[]|null $ids
     */
    public function __construct(Imap $imap, array $ids = null)
    {
        $this->imap = $imap;
        $this->ids = $ids;
    }

    /**
     * Sets iteration order
     *
     * @param bool $reverse Determines the iteration order. By default from newest messages to oldest
     *                      true for from newest messages to oldest
     *                      false for from oldest messages to newest
     */
    public function setIterationOrder($reverse)
    {
        $this->reverse = $reverse;
        $this->rewind();
    }

    /**
     * Return the current element
     *
     * @return Message
     */
    public function current()
    {
        $msgId = $this->ids === null
            ? $this->iterationPos
            : $this->ids[$this->iterationPos];

        return $this->imap->getMessage($msgId);
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        if ($this->reverse) {
            --$this->iterationPos;
        } else {
            ++$this->iterationPos;
        }
    }

    /**
     * Return the key of the current element
     *
     * @return int on success, or null on failure.
     */
    public function key()
    {
        return $this->iterationPos;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->iterationMin === null || $this->iterationMax === null) {
            if ($this->ids === null) {
                $this->iterationMin = 1;
                $this->iterationMax = $this->imap->count();
            } else {
                $this->iterationMin = 0;
                $this->iterationMax = count($this->ids) - 1;
            }
        }

        return $this->iterationPos !== null
            && $this->iterationPos >= $this->iterationMin
            && $this->iterationPos <= $this->iterationMax;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        if ($this->ids === null) {
            $this->iterationMin = 1;
            $this->iterationMax = $this->imap->count();
        } else {
            $this->iterationMin = 0;
            $this->iterationMax = count($this->ids) - 1;
        }
        if ($this->reverse) {
            $this->iterationPos = $this->iterationMax;
        } else {
            $this->iterationPos = $this->iterationMin;
        }
    }
}
