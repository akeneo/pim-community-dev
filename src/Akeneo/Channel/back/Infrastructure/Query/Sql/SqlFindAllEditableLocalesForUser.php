<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please edit the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Doctrine\DBAL\Connection;

class SqlFindAllEditableLocalesForUser implements FindAllEditableLocalesForUser
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findAll(int $userId): array
    {
        $sql = <<<SQL
            SELECT
                locale.code as localeCode,
                locale.is_activated AS isActivated
            FROM pim_catalog_locale locale
            JOIN pimee_security_locale_access locale_access ON locale.id = locale_access.locale_id
            JOIN oro_user_access_group user_access_group ON user_access_group.group_id = locale_access.user_group_id
            WHERE user_access_group.user_id = :userId
            AND locale_access.edit_products = 1
        SQL;

        $results = $this->connection
            ->executeQuery(
                $sql,
                ['userId' => $userId]
            )->fetchAllAssociative();

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
