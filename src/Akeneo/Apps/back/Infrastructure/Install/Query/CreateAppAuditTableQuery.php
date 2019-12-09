<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Install\Query;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CreateAppAuditTableQuery
{
    const QUERY = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_app_audit(
    id INT NOT NULL AUTO_INCREMENT,
    app_code VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    event_count INT NOT NULL,
    event_type ENUM('product_created', 'product_updated') NOT NULL,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_AUDIT_akeneo_app_audit_code FOREIGN KEY (app_code) REFERENCES akeneo_app (code),
    INDEX IDX_AUDIT_id (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
