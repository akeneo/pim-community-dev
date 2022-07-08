<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedFindAllEditableLocalesForUser implements FindAllEditableLocalesForUser, CachedQueryInterface
{
    private array $cache = [];

    public function __construct(
        private FindAllEditableLocalesForUser $findAllEditableLocalesForUser
    ) {
    }

    public function findAll(int $userId): array
    {
        if (!array_key_exists($userId, $this->cache)) {
            $this->cache[$userId] = $this->findAllEditableLocalesForUser->findAll($userId);
        }

        return $this->cache[$userId];
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
