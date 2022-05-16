<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindAllEditableLocalesForUser implements FindAllEditableLocalesForUser
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @return Locale[]
     */
    public function findAll(int $userId): array
    {
        $sql = <<<SQL
            SELECT
                locale.code as localeCode,
                locale.is_activated AS isActivated
            FROM pim_catalog_locale locale
        SQL;

        $results = $this->connection->executeQuery($sql)->fetchAllAssociative();
        $locales = [];

        foreach ($results as $result) {
            $locales[] = new Locale(
                $result['localeCode'],
                (bool) $result['isActivated'],
            );
        }

        return $locales;
    }
}
