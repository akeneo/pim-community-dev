<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;


use LRUCache\LRUCache;

class LruAttributeRepository
{
    /** @var AttributeRepository */
    private $attributeRepository;

    /** @var LRUCache */
    private $cache;

    public function __construct(AttributeRepository $attributeRepository, int $capacity)
    {
        $this->attributeRepository = $attributeRepository;
        $this->cache = new LRUCache($capacity);
    }

    public function findOneByIdentifier(string $code): ?Attribute
    {
        $attribute = $this->cache->get($code);
        if (null !== $attribute) {
            return $attribute;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        if (null !== $attribute) {
            $this->cache->put($code, $attribute);
        }

        return $attribute;
    }

    public function findSeveralByIdentifiers(array $codes): array
    {
        $cachedAttributes = [];
        $uncachedAttributeCodes = [];
        foreach ($codes as $code) {
            $attribute = $this->cache->get($code);
            if (null !== $attribute) {
                $cachedAttributes[$code] = $attribute;
            } else {
                $uncachedAttributeCodes[] = $code;
            }

        }

        $uncachedAttributes = $this->attributeRepository->findSeveralByIdentifiers($uncachedAttributeCodes);
        foreach ($uncachedAttributes as $code => $attribute) {
            $this->cache->put($code, $attribute);
        }

        return array_merge($cachedAttributes, $uncachedAttributes);
    }

}
