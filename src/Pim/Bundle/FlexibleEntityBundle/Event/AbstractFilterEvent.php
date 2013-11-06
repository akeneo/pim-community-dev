<?php

namespace Pim\Bundle\FlexibleEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Filter event allows to know the create flexible attribute
 *
 * @abstract
 */
abstract class AbstractFilterEvent extends Event
{
    /**
     * Flexible manager
     * @var FlexibleManager
     */
    protected $manager;

    /**
     * Constructor
     * @param FlexibleManager $manager
     */
    public function __construct(FlexibleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return FlexibleManager
     */
    public function getManager()
    {
        return $this->manager;
    }
}
