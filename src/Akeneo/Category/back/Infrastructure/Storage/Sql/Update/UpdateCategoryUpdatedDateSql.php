<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql\Update;

use Akeneo\Category\Domain\Query\UpdateCategoryUpdatedDate;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryUpdatedDateSql implements UpdateCategoryUpdatedDate
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function execute(string $categoryCode): void
    {
        $sql = <<<SQL
            UPDATE pim_catalog_category
            SET updated = NOW()
            WHERE code = :code
        SQL;

        $this->connection->executeQuery(
            $sql,
            ['code' => $categoryCode],
            ['code' => \PDO::PARAM_STR],
        );
    }
}
