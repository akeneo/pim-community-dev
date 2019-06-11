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
        $this->cache = new LRUCache(1000);
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
}
