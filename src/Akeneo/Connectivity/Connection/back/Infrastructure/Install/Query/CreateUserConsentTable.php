<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install\Query;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserConsentTable
{
    const QUERY = <<<'SQL'
    CREATE TABLE IF NOT EXISTS akeneo_connectivity_user_consent(
        user_id INT NOT NULL,
        app_id VARCHAR(36) NOT NULL,
        scopes JSON NOT NULL,
        consent_date DATETIME NOT NULL,
        PRIMARY KEY (user_id, app_id),
        CONSTRAINT FK_CONNECTIVITY_CONNECTION_user_consent_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id) ON DELETE CASCADE,
        CONSTRAINT FK_CONNECTIVITY_CONNECTION_user_consent_app_id FOREIGN KEY (app_id) REFERENCES akeneo_connectivity_connected_app (id) ON DELETE CASCADE
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
    SQL;
}
