<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCachedGetRequiredAttributesMasks implements GetRequiredAttributesMasks
{
    /** @var GetRequiredAttributesMasks */
    private $getRequiredAttributesMasks;

    /** @var LRUCache */
    private $cache;

    public function __construct(GetRequiredAttributesMasks $getRequiredAttributesMasks)
    {
        $this->getRequiredAttributesMasks = $getRequiredAttributesMasks;
        $this->cache = new LRUCache(500);
    }

    /**
     * {@inheritdoc}
     */
    public function fromFamilyCodes(array $familyCodes): array
    {
        if (empty($familyCodes)) {
            return [];
        }

        $fetchNonFoundFamilyCodes = function (array $notFoundFamilyCodes): array {
            return $this->getRequiredAttributesMasks->fromFamilyCodes($notFoundFamilyCodes);
        };

        return $this->cache->getForKeys($familyCodes, $fetchNonFoundFamilyCodes);
    }
}
