<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateConnectionTableQuery
{
    /**
     * /!\ WARNING /!\
     *
     * The anonymization script for cloned PIM instances must be updated after any change on the structure.
     * See `pim-enterprise-dev/deployments/bin/clone_serenity.sh`.
     */
    const QUERY = <<<'SQL'
    CREATE TABLE IF NOT EXISTS akeneo_connectivity_connection(
        client_id INT NOT NULL UNIQUE,
        user_id INT NOT NULL,
        code VARCHAR(100) NOT NULL PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        flow_type VARCHAR(50) NOT NULL DEFAULT 'other',
        image VARCHAR(255) DEFAULT NULL NULL,
        created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        auditable TINYINT(1) DEFAULT 0 NOT NULL,
        webhook_url VARCHAR(255) NULL,
        webhook_secret VARCHAR(255) NULL,
        webhook_enabled TINYINT(1) DEFAULT 0 NOT NULL,
        CONSTRAINT FK_CONNECTIVITY_CONNECTION_client_id FOREIGN KEY (client_id) REFERENCES pim_api_client (id),
        CONSTRAINT FK_CONNECTIVITY_CONNECTION_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
    SQL;
}
