<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LruCachedGetExistingAttributeOptionsWithValues implements GetExistingAttributeOptionsWithValues
{
    /** @var GetExistingAttributeOptionsWithValues */
    private $getExistingAttributeOptionsWithValues;

    /** @var LRUCache */
    private $cache;

    public function __construct(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
        $this->cache = new LRUCache(10000);
    }

    /**
     * {@inheritDoc}
     */
    public function fromAttributeCodeAndOptionCodes(array $optionKeys): array
    {
        if (empty($optionKeys)) {
            return [];
        }

        return $this->cache->getForKeys(
            $optionKeys,
            \Closure::fromCallable([$this->getExistingAttributeOptionsWithValues, 'fromAttributeCodeAndOptionCodes'])
        );
    }
}
