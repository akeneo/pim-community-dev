<?php

namespace Pim\Component\Resource\Domain\Event;

use Pim\Component\Resource\Domain\ResourceInterface;
use Pim\Component\Resource\Domain\ResourceSetInterface;

/**
 * Retrieve the event to linked to a resource or a set of resources.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventResolver
{
    /** @var EventRegistry */
    protected $registry;

    /**
     * @param ResourceInterface|ResourceSetInterface $resource
     *
     * @return ResourceEvent
     */
    public function resolves($resource)
    {
        if ($resource instanceof ResourceInterface) {
            $wantedEventType = get_class($resource);
        } elseif ($resource instanceof ResourceSetInterface) {
            $wantedEventType = $resource->getType();
        } else {
            throw new \InvalidArgumentException(
                'The resolver can only handle "ResourceInterface" and "ResourceSetInterface".'
            );
        }

        foreach ($this->registry as $eventType => $event)
        {
            if ($wantedEventType === $eventType) {
                return $event;
            }
        }

        if ($resource instanceof ResourceInterface) {
            return new ResourceEvent($resource);
        }

        return new ResourceBulkEvent($resource);
    }
}
