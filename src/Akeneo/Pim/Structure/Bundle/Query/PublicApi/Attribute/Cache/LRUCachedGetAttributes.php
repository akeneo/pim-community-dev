<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCachedGetAttributes implements GetAttributes
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var LRUCache */
    private $cache;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
        $this->resetCache();
    }

    /**
     * {@inheritdoc}
     */
    public function forCodes(array $attributeCodes): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        $fetchNonFoundAttributeCodes = function (array $attributesNotFound): array {
            return $this->getAttributes->forCodes($attributesNotFound);
        };

        return $this->cache->getForKeys($attributeCodes, $fetchNonFoundAttributeCodes);
    }

    /**
     * {@inheritdoc}
     *
     * This method does not use the forCodes method for performance reason
     */
    public function forCode(string $attributeCode): ?Attribute
    {
        $fetchNonFoundAttributeCodes = function (string $attributeCode): ?Attribute {
            return $this->getAttributes->forCode($attributeCode);
        };

        return $this->cache->getForKey($attributeCode, $fetchNonFoundAttributeCodes);
    }

    /**
     * You should never clear the cache except in very special cases. For example, when you delete an attribute in the same PHP process as creating/updating a product. 
     * It mainly occurs when you are testing the PIM, as you can load an attribute in the cache during the Arrange part of the the test, delete this attribute in Act part of the test, and try to fetch the attribute in the Assert part of the test.
     */
    public function resetCache(): void
    {
        $this->cache = new LRUCache(1000);
    }
}
