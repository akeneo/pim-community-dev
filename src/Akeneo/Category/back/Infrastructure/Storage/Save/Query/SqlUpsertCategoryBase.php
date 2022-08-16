<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;


/**
 * Save values from model into pim_catolog_category table:
 * The values are inserted if the id is new, they are updated if the id already exists.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlUpsertCategoryBase implements UpsertCategoryBase
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param Category $categoryModel
     * @return void
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function execute(Category $categoryModel): void
    {
        // TODO if the dev try to change code by passing an existing id, this updater won't change the code
        // TODO BUT it also won't notify the user that the code has not been changed
        if ($this->categoryAlreadyExistsByCode($categoryModel->getCode())) {
            $this->updateCategory($categoryModel);
        } else {
            $this->insertCategory($categoryModel);
        }
    }

    /**
     * @param Category $categoryModel
     * @return void
     * @throws Exception
     */
    private function insertCategory(Category $categoryModel): void
    {
        // TODO: At this stage of the development, because some columns have no default value,
        // TODO: the insert will use root=0 (then update to new generated id) and hardcoded values for lv, lft and rgt
        $query = <<< SQL
            INSERT INTO pim_catalog_category
                (parent_id, code, created, root, lvl, lft, rgt)
            VALUES
                (:parent_id, :code, NOW(), :root, :lvl, :lft, :rgt)
            ;
        SQL;

        // Note: root value has no meaning yet, it will be updated after insert query is executed
        $this->connection->executeQuery(
            $query,
            [
                'parent_id' => $categoryModel->getParentId()?->getValue(),
                'code' => (string) $categoryModel->getCode(),
                'root' => 0,
                'lvl' => 0,
                'lft' => 1,
                'rgt' => 2,
            ],
            [
                'parent_id' => \PDO::PARAM_INT,
                'code' => \PDO::PARAM_STR,
                'root' => \PDO::PARAM_INT,
                'lvl' => \PDO::PARAM_INT,
                'lft' => \PDO::PARAM_INT,
                'rgt' => \PDO::PARAM_INT,
            ]
        );

        // TODO: this is temporary until root value is well managed
        // We cannot access newly auto incremented id during the insert query. We have to update root in a second query
        $newCategoryId = $this->connection->lastInsertId();
        $this->connection->executeQuery(
            <<< SQL
                UPDATE pim_catalog_category
                SET root=:root
                WHERE code=:category_code
            SQL,
            [
                'category_code' => (string) $categoryModel->getCode(),
                'root' => $newCategoryId,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
                'root' => \PDO::PARAM_INT,
            ]
        );
    }

    /**
     * @param Category $categoryModel
     * @return void
     * @throws Exception
     */
    private function updateCategory(Category $categoryModel): void
    {
        // TODO: At this stage of the development, because some columns have no default value,
        // TODO: the update will set root=$categoryModel->getId()->getValue() and hardcoded values for lv, lft and rgt
        $query = <<< SQL
                UPDATE pim_catalog_category
                SET
                    parent_id = :parent_id,
                    created = pim_catalog_category.created,
                    updated = NOW(),
                    root = :root,
                    lvl = :lvl,
                    lft = :lft,
                    rgt = :rgt
                WHERE code = :category_code
                ;
            SQL;

        $this->connection->executeQuery(
            $query,
            [
                'category_code' => (string) $categoryModel->getCode(),
                'parent_id' => $categoryModel->getParentId()?->getValue(),
                'root' => $categoryModel->getId()->getValue(),
                'lvl' => 0,
                'lft' => 1,
                'rgt' => 2,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
                'parent_id' => \PDO::PARAM_INT,
                'root' => \PDO::PARAM_INT,
                'lvl' => \PDO::PARAM_INT,
                'lft' => \PDO::PARAM_INT,
                'rgt' => \PDO::PARAM_INT,
            ]
        );
    }

    /**
     * @param Code $code
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function categoryAlreadyExistsByCode(Code $code): bool
    {
        $query = <<< SQL
            SELECT code
            FROM pim_catalog_category
            WHERE code=:category_code
        SQL;

        return $this->connection->executeQuery(
            $query,
            [
                'category_code' => (string) $code
            ],
            [
                'category_code' => \PDO::PARAM_STR
            ],
        )->fetchOne();
    }
}
