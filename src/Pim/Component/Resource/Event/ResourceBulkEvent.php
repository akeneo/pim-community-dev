<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\ResourceSetInterface;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Default resource bulk event
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceBulkEvent extends BaseEvent implements ResourceBulkEventInterface
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
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->resources;
    }
}
