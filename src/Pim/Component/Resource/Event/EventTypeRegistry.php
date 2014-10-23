<?php

namespace Pim\Component\Resource\Event;

/**
 * Event registry.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventTypeRegistry
{
    /** @var ResourceEventInterface[] */
    protected $events = [];

    /**
     * @return ResourceEventInterface[]
     */
    public function getEventTypes()
    {
        return $this->events;
    }

    /**
     * @param ResourceEventInterface $eventType
     *
     * @return EventTypeRegistry
     */
    public function register(ResourceEventInterface $eventType)
    {
        $this->events[$eventType->getSubjectClass()] = $eventType;

        return $this;
    }
}
