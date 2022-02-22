<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Channel\Locale\Infrastructure\Query;

use Akeneo\Channel\Locale\API\Query\GetEditableLocaleCodes;
use Doctrine\DBAL\Connection;

final class SqlGetEditableLocaleCodes implements GetEditableLocaleCodes
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(int $userId): array
    {
        $sql = <<<SQL
        SELECT DISTINCT l.code
        FROM pim_catalog_locale l
            INNER JOIN pimee_security_locale_access la ON l.id = la.locale_id
            INNER JOIN oro_user_access_group ug ON ug.group_id = la.user_group_id
        WHERE ug.user_id = :user_id
            AND la.edit_products = 1
            AND l.is_activated = 1
        SQL;

        return $this->connection->fetchFirstColumn($sql, ['user_id' => $userId]);
    }
}
