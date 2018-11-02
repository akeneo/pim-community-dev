<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;


use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\DBAL\Connection;
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

    public function findOneByIdentifier(string $code): ?AttributeInterface
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

}
