<?php

namespace Oro\Bundle\ImapBundle\Manager;

use Oro\Bundle\ImapBundle\Connector\ImapMessageIterator;
use Oro\Bundle\ImapBundle\Manager\DTO\Email;

class ImapEmailIterator implements \Iterator, \Countable
{
    /**
     * @var ImapMessageIterator
     */
    private $iterator;

    /**
     * @var ImapEmailManager
     */
    private $manager;

    /**
     * Constructor
     *
     * @param ImapMessageIterator $iterator
     * @param ImapEmailManager $manager
     */
    public function __construct(ImapMessageIterator $iterator, ImapEmailManager $manager)
    {
        $this->iterator = $iterator;
        $this->manager = $manager;
    }

    /**
     * Sets iteration order
     *
     * @param bool $reverse Determines the iteration order. By default from newest emails to oldest
     *                      true for from newest emails to oldest
     *                      false for from oldest emails to newest
     */
    public function setIterationOrder($reverse)
    {
        $this->iterator->setIterationOrder($reverse);
    }

    /**
     * The number of emails in this iterator
     *
     * @return int
     */
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * Return the current element
     *
     * @return Email
     */
    public function current()
    {
        return $this->manager->convertToEmail($this->iterator->current());
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * Return the key of the current element
     *
     * @return int on success, or null on failure.
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }
}
