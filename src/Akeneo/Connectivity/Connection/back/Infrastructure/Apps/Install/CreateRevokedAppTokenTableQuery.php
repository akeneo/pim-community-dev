<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Install;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateRevokedAppTokenTableQuery
{
    public const QUERY = <<<'SQL'
        CREATE TABLE IF NOT EXISTS akeneo_connectivity_revoked_app_token (
            token VARCHAR(255) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;
}
