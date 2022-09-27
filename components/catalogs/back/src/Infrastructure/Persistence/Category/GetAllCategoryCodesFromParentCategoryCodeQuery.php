<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Application\Persistence\Category\GetAllCategoryCodesFromParentCategoryCodeQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCategoryCodesFromParentCategoryCodeQuery implements GetAllCategoryCodesFromParentCategoryCodeQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return string[]
     */
    public function execute(string $parentCategoryCode): array
    {
        $query = <<<SQL
            WITH RECURSIVE cte (id, code, parent_id) as (
                SELECT
                    id,
                    code,
                    parent_id
                FROM pim_catalog_category
                WHERE code = :parentCategoryCode
                UNION ALL
                select
                    p.id,
                    p.code,
                    p.parent_id
                FROM pim_catalog_category p
                INNER JOIN cte
                ON p.parent_id = cte.id
            )
            SELECT code FROM cte
        SQL;

        /** @var string[] $categoryCodes */
        $categoryCodes = $this->connection->executeQuery($query, [
            'parentCategoryCode' => $parentCategoryCode,
        ])->fetchFirstColumn();

        return $categoryCodes;
    }
}
