<?php


namespace Pim\Component\Resource\Domain\Manager;

use Pim\Component\Resource\Domain\Event\EventResolver;
use Pim\Component\Resource\Domain\Event\ResourceEvents;
use Pim\Component\Resource\Domain\ResourceInterface;
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
    public function save(ResourceInterface $resource)
    {
        $this->dispatch($resource, ResourceEvents::PRE_SAVE);
        if ($resource->isNew()) {
            $this->create($resource);
        } else {
            $this->update($resource);
        }
        $this->dispatch($resource, ResourceEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ResourceInterface $resource)
    {
        $this->dispatch($resource, ResourceEvents::PRE_DELETE);
        $this->resourceManager->delete($resource);
        $this->dispatch($resource, ResourceEvents::POST_DELETE);
    }

    /**
     * Creates a new resource.
     *
     * @param ResourceInterface $resource
     */
    protected function create(ResourceInterface $resource)
    {
        $this->dispatch($resource, ResourceEvents::PRE_CREATE);
        $this->resourceManager->save($resource);
        $this->dispatch($resource, ResourceEvents::POST_CREATE);
    }

    /**
     * Updates an existing resource.
     *
     * @param ResourceInterface $resource
     */
    protected function update(ResourceInterface $resource)
    {
        $this->dispatch($resource, ResourceEvents::PRE_CREATE);
        $this->resourceManager->save($resource);
        $this->dispatch($resource, ResourceEvents::POST_CREATE);
    }

    /**
     * Dispatchs a resource event.
     *
     * @param ResourceInterface $resource
     * @param string            $type
     */
    private function dispatch(ResourceInterface $resource, $type)
    {
        $event = $this->eventResolver->resolves($resource);
        $this->eventDispatcher->dispatch($type, $event);
    }
}
