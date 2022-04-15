<?php


namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindLocales implements FindLocales, CachedQueryInterface
{
    private ?array $indexedCache = null;
    private ?array $cache = null;

    public function __construct(
        private Connection $connection
    ) {
    }

    public function find(string $localeCode): ?Locale
    {
        if (null === $this->indexedCache || !$this->isLocaleCached($localeCode)) {
            $sql = <<<SQL
                SELECT 
                    l.code AS localeCode, 
                    l.is_activated AS isActivated
                FROM pim_catalog_locale l
                WHERE l.code = :localeCode
            SQL;

            $result = $this->connection->executeQuery($sql, ['localeCode' => $localeCode])->fetchAssociative();
            $this->indexedCache[$localeCode] = null;

            if ($result) {
                $this->indexedCache[$localeCode] = new Locale(
                    $result['localeCode'],
                    $result['isActivated']
                );
            }
        }

        return $this->indexedCache[$localeCode];
    }

    public function findAllActivated(): array
    {
        if (null === $this->cache) {
            $sql = <<<SQL
                SELECT 
                    l.code AS localeCode, 
                    l.is_activated AS isActivated
                FROM pim_catalog_locale l
                WHERE l.is_activated = 1
            SQL;

            $results = $this->connection->executeQuery($sql)->fetchAllAssociative();
            $this->cache = [];

            foreach ($results as $result) {
                $locale = new Locale(
                    $result['localeCode'],
                    $result['isActivated']
                );

                $this->cache[] = $locale;
            }
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
