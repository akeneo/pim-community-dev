<?php

namespace Pim\Component\Resource\Domain\Event;

use Pim\Component\Resource\Domain\ResourceSetInterface;

/**
 * Default resource bulk event
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceBulkEvent
{
    /** @var ResourceSetInterface */
    protected $resources;

    /**
     * @param ResourceSetInterface $resources
     */
    public function __construct(ResourceSetInterface $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @return ResourceSetInterface
     */
    public function getResources()
    {
        return $this->resources;
    }
}
