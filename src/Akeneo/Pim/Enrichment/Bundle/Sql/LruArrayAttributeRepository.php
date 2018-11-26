<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

use cash\LRUCache;

class LruArrayAttributeRepository
{
    private const NOT_FOUND = 'NOT_FOUND';

    private const NOT_EXISTING = 'NOT_EXISTING';

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
        $attributes = $this->findSeveralByIdentifiers([$code]);

        return $attributes[$code];
    }

    public function findSeveralByIdentifiers(array $codes): array
    {
        $cachedAttributes = [];
        $uncachedAttributeCodes = [];
        foreach ($codes as $code) {
            $attribute = $this->cache->get($code, self::NOT_FOUND);
            if (self::NOT_FOUND !== $attribute) {
                $cachedAttributes[$code] = self::NOT_EXISTING === $attribute ? null : $attribute;
            } else {
                $uncachedAttributeCodes[] = $code;
            }
        }

        if (empty($uncachedAttributeCodes)) {
            return $cachedAttributes;
        }

        $uncachedAttributes = $this->attributeRepository->findSeveralByIdentifiers($uncachedAttributeCodes);
        foreach ($uncachedAttributes as $code => $attribute) {
            $attribute = null === $attribute ? self::NOT_EXISTING : $attribute;
            $this->cache->put($code, $attribute);
        }

        return array_merge($cachedAttributes, $uncachedAttributes);
    }

}
