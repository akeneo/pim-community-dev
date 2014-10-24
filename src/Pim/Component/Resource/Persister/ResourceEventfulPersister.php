<?php

namespace Pim\Component\Resource\Persister;

use Pim\Component\Resource\Event\EventResolver;
use Pim\Component\Resource\Event\ResourceEvents;
use Pim\Component\Resource\Model\ResourceInterface;
use Pim\Component\Resource\Model\ResourceSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Resource persister able to dispatch events.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceEventfulPersister implements ResourcePersisterInterface
{
    /** @var ResourcePersisterInterface */
    protected $resourcePersister;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var EventResolver */
    protected $eventResolver;

    /**
     * @param ResourcePersisterInterface $resourcePersister
     * @param EventDispatcherInterface   $eventDispatcher
     * @param EventResolver              $eventResolver
     */
    public function __construct(
        ResourcePersisterInterface $resourcePersister,
        EventDispatcherInterface $eventDispatcher,
        EventResolver $eventResolver
    ) {
        $this->resourcePersister = $resourcePersister;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventResolver = $eventResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch(ResourceEvents::PRE_SAVE, $resource);
        if ($resource->isNew()) {
            $this->create($resource, $andFlush);
        } else {
            $this->update($resource, $andFlush);
        }
        $this->dispatch(ResourceEvents::POST_SAVE, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkSave(ResourceSetInterface $resources, $andFlush = true)
    {
        $this->dispatch(ResourceEvents::PRE_BULK_SAVE, $resources);
        $this->resourcePersister->bulkSave($resources, $andFlush);
        $this->dispatch(ResourceEvents::POST_BULK_SAVE, $resources);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch(ResourceEvents::PRE_DELETE, $resource);
        $this->resourcePersister->delete($resource, $andFlush);
        $this->dispatch(ResourceEvents::POST_DELETE, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkDelete(ResourceSetInterface $resources, $andFlush = true)
    {
        $this->dispatch(ResourceEvents::PRE_BULK_DELETE, $resources);
        $this->resourcePersister->bulkDelete($resources, $andFlush);
        $this->dispatch(ResourceEvents::POST_BULK_DELETE, $resources);
    }

    /**
     * {@inheritdoc}
     */
    public function createResourceSet(array $resources)
    {
        return $this->resourcePersister->createResourceSet($resources);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectManagerTransitional($class)
    {
        return $this->resourcePersister->getObjectManagerTransitional($class);
    }

    /**
     * Creates a new resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $andFlush
     */
    protected function create(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch(ResourceEvents::PRE_CREATE, $resource);
        $this->resourcePersister->save($resource, $andFlush);
        $this->dispatch(ResourceEvents::POST_CREATE, $resource);
    }

    /**
     * Updates an existing resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $andFlush
     */
    protected function update(ResourceInterface $resource, $andFlush = true)
    {
        $this->dispatch(ResourceEvents::PRE_CREATE, $resource);
        $this->resourcePersister->save($resource, $andFlush);
        $this->dispatch(ResourceEvents::POST_CREATE, $resource);
    }

    /**
     * Dispatchs a resource event.
     *
     * @param string               $type
     * @param ResourceSetInterface $resource
     */
    private function dispatch($type, $resource)
    {
        $event = $this->eventResolver->resolve($resource);
        $this->eventDispatcher->dispatch($type, $event);
    }
}
