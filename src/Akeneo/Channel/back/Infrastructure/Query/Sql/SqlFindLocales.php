<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindLocales implements FindLocales
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function find(string $localeCode): ?Locale
    {
        $sql = <<<SQL
            SELECT 
                l.code AS localeCode, 
                l.is_activated AS isActivated
            FROM pim_catalog_locale l
            WHERE l.code = :localeCode
        SQL;

        $result = $this->connection->executeQuery($sql, ['localeCode' => $localeCode])->fetchAssociative();

        if ($result) {
            return new Locale(
                $result['localeCode'],
                (bool) $result['isActivated'],
            );
        }

        return null;
    }

    /**
     * @return Locale[]
     */
    public function findAllActivated(): array
    {
        $sql = <<<SQL
            SELECT 
                l.code AS localeCode, 
                l.is_activated AS isActivated
            FROM pim_catalog_locale l
            WHERE l.is_activated = 1
        SQL;

        $results = $this->connection->executeQuery($sql)->fetchAllAssociative();
        $locales = [];

        foreach ($results as $result) {
            $locales[] = new Locale(
                $result['localeCode'],
                (bool) $result['isActivated']
            );
        }

        return $locales;
    }
}
