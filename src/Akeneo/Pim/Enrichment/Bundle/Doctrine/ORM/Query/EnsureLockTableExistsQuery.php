<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Lock\Query\EnsureLockTableExistsInterface;
use Doctrine\DBAL\Connection;

/**
 * TODO pull up remove this
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EnsureLockTableExistsQuery implements EnsureLockTableExistsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS lock_keys (
    key_id VARCHAR(64) NOT NULL PRIMARY KEY,
    key_token VARCHAR(44) NOT NULL,
    key_expiration INTEGER UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->connection->exec($sql);
    }
}
