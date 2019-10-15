<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Install\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateAppsTableQuery
{
    const QUERY = <<<'SQL'
CREATE TABLE IF NOT EXISTS akeneo_app(
    code VARCHAR(100) NOT NULL,
    label VARCHAR(100) NOT NULL,
    flow_type ENUM('data_destination', 'data_source', 'others') NOT NULL DEFAULT 'others',
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_APP_code (code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
