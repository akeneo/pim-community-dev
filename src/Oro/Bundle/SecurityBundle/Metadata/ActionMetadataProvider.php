<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Metadata;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class ActionMetadataProvider
{
    const CACHE_NAMESPACE = 'AclAction';
    const CACHE_KEY = 'data';

    protected AclAnnotationProvider $annotationProvider;
    protected ?AdapterInterface $cache = null;

    /**
     * @var ?array
     *         key = action name
     *         value = ActionMetadata
     */
    protected ?array $localCache = null;

    public function __construct(
        AclAnnotationProvider $annotationProvider,
        ?AdapterInterface $cache = null
    ) {
        $this->annotationProvider = $annotationProvider;
        $this->cache = $cache;
    }

    /**
     * Checks whether an action with the given name is defined.
     */
    public function isKnownAction(string $actionName): bool
    {
        $this->ensureMetadataLoaded();

        return isset($this->localCache[$actionName]);
    }

    /**
     * Gets metadata for all actions.
     *
     * @return ActionMetadata[]
     */
    public function getActions(): array
    {
        $this->ensureMetadataLoaded();

        return array_values($this->localCache);
    }

    public function warmUpCache(): void
    {
        $this->ensureMetadataLoaded();
    }

    public function clearCache(): void
    {
        if ($this->cache) {
            $this->cache->delete(self::CACHE_KEY);
        }
        $this->localCache = null;
    }

    /**
     * Makes sure that metadata are loaded
     */
    protected function ensureMetadataLoaded(): void
    {
        if ($this->localCache === null) {
            $data = null;
            if ($this->cache) {
                $data = $this->cache->getItem(self::CACHE_KEY)->get();
            }
            if (!$data) {
                $data = [];
                foreach ($this->annotationProvider->getAnnotations('action') as $annotation) {
                    $data[$annotation->getId()] = new ActionMetadata(
                        $annotation->getId(),
                        $annotation->getGroup(),
                        $annotation->getLabel(),
                        $annotation->isEnabledAtCreation(),
                        $annotation->getOrder(),
                    );
                }

                if ($this->cache) {
                    $item = $this->cache->getItem(self::CACHE_KEY);
                    $item->set($data);
                }
            }

            $this->localCache = $data;
        }
    }
}
