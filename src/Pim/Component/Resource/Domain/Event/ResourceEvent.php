<?php

namespace Pim\Component\Resource\Domain\Event;

use Pim\Component\Resource\Domain\ResourceInterface;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Default resource event
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceEvent extends BaseEvent implements ResourceEventInterface
{
    /** @var ResourceInterface */
    protected $resource;

    /**
     * @param ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource;
    }
}
