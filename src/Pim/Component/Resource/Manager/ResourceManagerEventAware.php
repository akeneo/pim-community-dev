<?php

namespace Pim\Component\Resource\Manager;

use Pim\Component\Resource\Event\EventResolver;
use Pim\Component\Resource\Event\ResourceEvents;
use Pim\Component\Resource\ResourceInterface;
use Pim\Component\Resource\ResourceSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Resource manager able to dispatch events.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceManagerEventAware implements ResourceManagerInterface
{
    /** @var ResourceManagerInterface */
    protected $resourceManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var EventResolver */
    protected $eventResolver;

    /**
     * {@inheritdoc}
     */
    public function save(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch($resource, ResourceEvents::PRE_SAVE);
        if ($resource->isNew()) {
            $this->create($resource, $andFlush);
        } else {
            $this->update($resource, $andFlush);
        }
        $this->dispatch($resource, ResourceEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkSave(ResourceSetInterface $resources, $andFlush = true)
    {
        $this->dispatch($resources, ResourceEvents::PRE_BULK_SAVE);
        $this->resourceManager->bulkSave($resources, $andFlush);
        $this->dispatch($resources, ResourceEvents::POST_BULK_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch($resource, ResourceEvents::PRE_DELETE);
        $this->resourceManager->delete($resource, $andFlush);
        $this->dispatch($resource, ResourceEvents::POST_DELETE);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkDelete(ResourceSetInterface $resources, $andFlush = true)
    {
        $this->dispatch($resources, ResourceEvents::PRE_BULK_DELETE);
        $this->resourceManager->bulkDelete($resources, $andFlush);
        $this->dispatch($resources, ResourceEvents::POST_BULK_DELETE);
    }

    /**
     * Creates a new resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $andFlush
     */
    protected function create(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch($resource, ResourceEvents::PRE_CREATE);
        $this->resourceManager->save($resource, $andFlush);
        $this->dispatch($resource, ResourceEvents::POST_CREATE);
    }

    /**
     * Updates an existing resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $andFlush
     */
    protected function update(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch($resource, ResourceEvents::PRE_CREATE);
        $this->resourceManager->save($resource, $andFlush);
        $this->dispatch($resource, ResourceEvents::POST_CREATE);
    }

    /**
     * Dispatchs a resource event.
     *
     * @param ResourceInterface|ResourceSetInterface $resource
     * @param string                                 $type
     */
    private function dispatch($resource, $type)
    {
        $event = $this->eventResolver->resolves($resource);
        $this->eventDispatcher->dispatch($type, $event);
    }
}
