<?php

namespace Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedFindLocales implements FindLocales, CachedQueryInterface
{
    private ?array $indexedCache = null;
    private ?array $cache = null;

    public function __construct(
        private FindLocales $findLocales
    ) {
    }

    public function find(string $localeCode): ?Locale
    {
        if (null === $this->indexedCache || !$this->isLocaleCached($localeCode)) {
            $this->indexedCache[$localeCode] = $this->findLocales->find($localeCode);
        }

        return $this->indexedCache[$localeCode];
    }

    public function findAllActivated(): array
    {
        if (null === $this->cache) {
            $this->cache = $this->findLocales->findAllActivated();
        }

        return $this->cache;
    }

    public function clearCache(): void
    {
        $this->indexedCache = null;
        $this->cache = null;
    }

    private function isLocaleCached(string $localeCode): bool
    {
        return key_exists($localeCode, $this->indexedCache);
    }
}
