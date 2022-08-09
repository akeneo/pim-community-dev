<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Update;

use Akeneo\Category\Application\Storage\Update\UpsertCategoryProperties;
use Akeneo\Category\Domain\Model\Category;
use Doctrine\DBAL\Connection;


/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlUpsertCategoryProperties implements UpsertCategoryProperties
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(Category $categoryModel): void
    {
        $query = <<<SQL
            INSERT INTO pim_catalog_category
                (id, parent_id, code)
            VALUES
                (:id, :parent_id, :code)
            ON DUPLICATE KEY UPDATE
                id = :id,
                parent_id = :parent_id,
                code = :code
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => $categoryModel->getId(),
                'parent_id' => $categoryModel->getParentId(),
                'code' => $categoryModel->getCode(),
            ]
        );
    }
}
