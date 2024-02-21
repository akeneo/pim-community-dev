<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Install;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTestAppTableQuery
{
    public const QUERY = <<<SQL
        CREATE TABLE IF NOT EXISTS akeneo_connectivity_test_app(
            client_id VARCHAR(36) NOT NULL PRIMARY KEY,
            client_secret VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            activate_url VARCHAR(255) NOT NULL,
            callback_url VARCHAR(255) NOT NULL,
            user_id INT DEFAULT NULL,
            CONSTRAINT `FK_TESTAPPUSERID` FOREIGN KEY (`user_id`) REFERENCES `oro_user` (`id`) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
    SQL;
}
