<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeTypes\Sql;

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

        $indexAttributesByCode = function (array $attributes): array {
            $result = [];
            foreach ($attributes as $attribute) {
                $result[$attribute->code()] = $attribute;
            }

            return $result;
        };

        return array_filter($this->cache->getOrSave($attributeCodes, $fetchNonFoundAttributeCodes, $indexAttributesByCode, null));
    }

    /**
     * {@inheritdoc}
     *
     * This method does not use the forCodes method for performance reason
     */
    public function forCode(string $attributeCode): ?Attribute
    {
        $default = 'DEFAULT_VALUE';

        $attribute = $this->cache->getOrElse($attributeCode, $default);
        if ($attribute !== $default) {
            return $attribute;
        }

        $attribute = $this->getAttributes->forCode($attributeCode);
        $this->cache->put($attributeCode, $attribute);

        return $attribute;
    }
}
