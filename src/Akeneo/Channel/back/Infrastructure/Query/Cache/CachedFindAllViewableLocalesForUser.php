<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedFindAllViewableLocalesForUser implements FindAllViewableLocalesForUser, CachedQueryInterface
{
    private ?array $cache = null;

    public function __construct(
        private FindAllViewableLocalesForUser $findAllViewableLocalesForUser
    ) {
    }

    public function findAll(int $userId): array
    {
        if (null === $this->cache) {
            $this->cache = $this->findAllViewableLocalesForUser->findAll($userId);
        }

        return $this->cache;
    }

    public function clearCache(): void
    {
        $this->cache = null;
    }
}
