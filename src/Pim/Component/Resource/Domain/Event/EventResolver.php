<?php

namespace Pim\Component\Resource\Domain\Event;

use Pim\Component\Resource\Domain\ResourceInterface;

/**
 * Retrieve the event to linked to a resource.
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
     * @param ResourceInterface $resource
     *
     * @return ResourceEvent
     */
    public function resolves(ResourceInterface $resource)
    {
        foreach ($this->registry as $className => $event)
        {
            if (get_class($resource) === $className) {
                return $event;
            }
        }

        return new ResourceEvent($resource);
    }
}
