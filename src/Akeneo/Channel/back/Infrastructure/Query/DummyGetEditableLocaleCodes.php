<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyGetEditableLocaleCodes implements GetEditableLocaleCodes
{
    public function __construct(private Connection $connection)
    {
    }

    public function forUserId(int $userId): array
    {
        $sql = 'SELECT code FROM pim_catalog_locale l WHERE l.is_activated = 1';

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }
}
