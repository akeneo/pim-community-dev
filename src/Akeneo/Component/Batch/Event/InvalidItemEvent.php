<?php

namespace Akeneo\Component\Batch\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Invalid Item Event
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class InvalidItemEvent extends Event implements EventInterface
{
    /** @var string */
    protected $class;

    /** @var string */
    protected $reason;

    /** @var array */
    protected $reasonParameters;

    /** @var array */
    protected $item;

    /**
     * Constructor
     *
     * @param string $class
     * @param string $reason
     * @param array  $reasonParameters
     * @param array  $item
     */
    public function __construct($class, $reason, array $reasonParameters, array $item)
    {
        $this->class = $class;
        $this->reason = $reason;
        $this->reasonParameters = $reasonParameters;
        $this->item = $item;
    }

    /**
     * Get the class which encountered the invalid item
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the reason why the item is invalid
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Get the reason parameters
     *
     * @return array
     */
    public function getReasonParameters()
    {
        return $this->reasonParameters;
    }

    /**
     * Get the invalid item
     *
     * @return array
     */
    public function getItem()
    {
        return $this->item;
    }
}
