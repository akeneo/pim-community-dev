<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Install;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateAppTableQuery
{
    public const QUERY = <<<'SQL'
    CREATE TABLE IF NOT EXISTS akeneo_connectivity_connected_app(
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        logo VARCHAR(255),
        author VARCHAR(255),
        partner VARCHAR(255) DEFAULT NULL NULL,
        categories JSON NOT NULL,
        certified TINYINT(1) DEFAULT 0 NOT NULL,
        connection_code VARCHAR(100) NOT NULL,
        scopes JSON NOT NULL,
        user_group_name VARCHAR(255) NOT NULL,
        has_outdated_scopes TINYINT DEFAULT 0 NOT NULL,
        created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT FK_CONNECTIVITY_CONNECTED_APP_connection_code FOREIGN KEY (connection_code) REFERENCES akeneo_connectivity_connection (code),
        CONSTRAINT FK_CONNECTIVITY_CONNECTED_APP_user_group_name FOREIGN KEY (user_group_name) REFERENCES oro_access_group (name)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
    SQL;
}
