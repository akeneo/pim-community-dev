<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\Cache;

use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

final class LRUCachedGetViewableAttributeCodesForUser implements GetViewableAttributeCodesForUserInterface
{
    private GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser;

    private LRUCache $cache;

    private ?int $userId = null;

    public function __construct(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $this->getViewableAttributeCodesForUser = $getViewableAttributeCodesForUser;
    }

    public function forAttributeCodes(array $attributeCodes, int $userId): array
    {
        if (empty($attributeCodes)) {
            return [];
        }

        if ($userId !== $this->userId) {
            $this->resetCache();
            $this->userId = $userId;
        }

        $fetchNonFoundAttributeCodes = function (array $nonCachedAttributeCodes) use ($userId): array {
            $result = array_fill_keys($nonCachedAttributeCodes, false);
            $grantedAttributeCodes = $this->getViewableAttributeCodesForUser->forAttributeCodes(
                $nonCachedAttributeCodes,
                $userId
            );
            foreach ($grantedAttributeCodes as $grantedAttributeCode) {
                $result[$grantedAttributeCode] = true;
            }

            return $result;
        };

        $grantedAttributeCodes = array_keys(
            array_filter($this->cache->getForKeys($attributeCodes, $fetchNonFoundAttributeCodes))
        );

        return array_map('strval', $grantedAttributeCodes);
    }

    public function resetCache(): void
    {
        $this->cache = new LRUCache(1000);
    }
}
