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

namespace Akeneo\Channel\Infrastructure\Query;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Doctrine\DBAL\Connection;

// @todo: should be tested ? If yes how to test this only in CE ?
// in CE the SQL query return all activated locales
// in EE the SQL query return activated locales on which

final class DumbGetEditableLocaleCodes implements GetEditableLocaleCodes
{
    public function __construct(private Connection $connection)
    {
    }

    public function forUserId(int $userId): array
    {
        $sql = <<<SQL
        SELECT code
        FROM pim_catalog_locale l
        WHERE l.is_activated = 1
        SQL;

        $queryResult = $this->connection->fetchAllAssociative($sql);

        $result = [];
        foreach ($queryResult as $data) {
            $result[] = $data['code'] ?? null;
        }

        return $result;
    }
}
