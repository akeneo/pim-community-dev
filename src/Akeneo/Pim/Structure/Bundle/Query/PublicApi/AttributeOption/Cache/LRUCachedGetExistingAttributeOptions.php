<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

/**
 * Cached version of Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes
 * It uses a LRUCache which stores existing attribute options with `true`, and unexisting ones with `false`
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCachedGetExistingAttributeOptions implements GetExistingAttributeOptionCodes
{
    /** @var GetExistingAttributeOptionCodes */
    private $getExistingOptionCodes;

    /** @var LRUCache */
    private $cache;

    public function __construct(GetExistingAttributeOptionCodes $getExistingOptionCodes)
    {
        $this->getExistingOptionCodes = $getExistingOptionCodes;
        $this->cache = new LRUCache(10000);
    }

    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array
    {
        if (empty($optionCodesIndexedByAttributeCodes)) {
            return [];
        }

        $fetchNonCachedAttributeOptions = function (array $nonCachedAttributeOptionKeys): array {
            if ([] === $nonCachedAttributeOptionKeys) {
                return [];
            }

            $results = array_fill_keys($nonCachedAttributeOptionKeys, '');
            $existingAttributeOptionCodes = $this->getExistingOptionCodes->fromOptionCodesByAttributeCode(
                $this->fromCacheKeys($nonCachedAttributeOptionKeys)
            );
            $existingKeys = array_combine(
                $this->toCacheKeys($existingAttributeOptionCodes),
                $this->normalizeExistingAttributeOptionsResults($existingAttributeOptionCodes)
            );

            return array_replace($results, $existingKeys);
        };

        $values = $this->cache->getForKeys(
            $this->toCacheKeys($optionCodesIndexedByAttributeCodes),
            $fetchNonCachedAttributeOptions
        );

        return $this->denormalizeExistingAttributeOptionsResults(array_unique(array_values(array_filter($values))));
    }

    /**
     * Converts an array of option codes indexed by attribute code to an array of keys usable by the LRUCache
     * e.g:
     * [
     *    'color' => ['blue', 'GREEN'],
     *    'size' => ['xs'],
     * ]
     * will be converted to ['color.blue', 'color.green', 'size.xs']
     * The keys are always in lowercase.
     */
    private function toCacheKeys(array $optionCodesIndexedByAttributeCode): array
    {
        $keys = [];
        foreach ($optionCodesIndexedByAttributeCode as $attributeCode => $optionCodes) {
            foreach ($optionCodes as $optionCode) {
                $keys[] = strtolower(sprintf('%s.%s', $attributeCode, $optionCode));
            }
        }

        return $keys;
    }

    /**
     * Performs the reverse operation from `toCacheKeys()` method
     */
    private function fromCacheKeys(array $cacheKeys): array
    {
        $optionsIndexedByAttributeCode = [];
        foreach ($cacheKeys as $cacheKey) {
            [$attributeCode, $optionCode] = explode('.', $cacheKey);
            $optionsIndexedByAttributeCode[$attributeCode][] = $optionCode;
        }

        return $optionsIndexedByAttributeCode;
    }

    /**
     * e.g:
     * [
     *    'color' => ['blue', 'GREEN'],
     *    'size' => ['xs'],
     * ]
     * will be converted to ['color.blue', 'color.GREEN', 'size.xs']
     * This method keeps the original cases.
     */
    private function normalizeExistingAttributeOptionsResults(array $optionCodesIndexedByAttributeCode): array
    {
        $results = [];
        foreach ($optionCodesIndexedByAttributeCode as $attributeCode => $optionCodes) {
            foreach ($optionCodes as $optionCode) {
                $results[] = sprintf('%s.%s', $attributeCode, $optionCode);
            }
        }

        return $results;
    }

    /**
     * Performs the reverse operation from `normalizeExistingAttributeOptionsResults()` method
     */
    private function denormalizeExistingAttributeOptionsResults(array $normalizedResults): array
    {
        $results = [];
        foreach ($normalizedResults as $normalizedResult) {
            [$attributeCode, $optionCode] = explode('.', $normalizedResult);
            $results[$attributeCode][] = $optionCode;
        }

        return $results;
    }
}
