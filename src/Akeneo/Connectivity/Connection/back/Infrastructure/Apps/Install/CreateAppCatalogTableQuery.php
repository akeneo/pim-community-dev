<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Install;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateAppCatalogTableQuery
{
    const QUERY = <<<'SQL'
    CREATE TABLE IF NOT EXISTS akeneo_connectivity_connected_app_catalogs (
        catalog_id BINARY(16) NOT NULL,
        connected_app_id VARCHAR(36) NOT NULL,
        PRIMARY KEY (catalog_id, connected_app_id),
        INDEX (connected_app_id),
        FOREIGN KEY (catalog_id) REFERENCES akeneo_catalog(id) ON DELETE CASCADE,
        FOREIGN KEY (connected_app_id) REFERENCES akeneo_connectivity_connected_app(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    SQL;
}
