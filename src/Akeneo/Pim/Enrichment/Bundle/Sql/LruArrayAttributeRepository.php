<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

class LruArrayAttributeRepository
{
    private const NOT_EXISTING = 'NOT_EXISTING';

    /** @var AttributeRepository */
    private $attributeRepository;

    /** @var LRUCache */
    private $cache;

    /** @var string */
    private $identifierCode;

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
            $attribute = $this->cache->get($code);
            if (null !== $attribute) {
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
            $this->cache->put($code, $attribute ?? self::NOT_EXISTING);
        }

        return array_merge($cachedAttributes, $uncachedAttributes);
    }



    /**
     * Get the identifier code
     *
     * @return string
     */
    public function getIdentifierCode(): string
    {
        if (null === $this->identifierCode) {
            $this->identifierCode = $this->attributeRepository->getIdentifierCode();
        }

        return $this->identifierCode;
    }

    /**
     * Get the identifier attribute
     * Only one identifier attribute can exist
     *
     * @return Attribute
     */
    public function getIdentifier(): Attribute
    {
        return $this->findOneByIdentifier($this->getIdentifierCode());
    }
}
