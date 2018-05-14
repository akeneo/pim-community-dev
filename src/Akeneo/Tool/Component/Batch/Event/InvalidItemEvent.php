<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
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

    /** @var InvalidItemInterface */
    protected $item;

    /**
     * @param InvalidItemInterface  $item
     * @param string                $class
     * @param string                $reason
     * @param array                 $reasonParameters
     */
    public function __construct(InvalidItemInterface $item, $class, $reason, array $reasonParameters)
    {
        $this->item = $item;
        $this->class = $class;
        $this->reason = $reason;
        $this->reasonParameters = $reasonParameters;
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
     * @return InvalidItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }
}
