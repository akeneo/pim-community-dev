<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\IsCategoryTreeLinkedToTemplate;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCategoryTreeLinkedToTemplateSql implements IsCategoryTreeLinkedToTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param CategoryId $categoryTreeId
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(CategoryId $categoryTreeId): bool
    {
        $query = <<<SQL
            SELECT * FROM pim_catalog_category_tree_template
            WHERE category_tree_id=:category_id
                AND template_uuid IS NOT NULL
        SQL;

        return (bool) $this->connection->executeQuery(
            $query,
            ['category_id' => $categoryTreeId->getValue()],
            ['category_id' => \PDO::PARAM_INT],
        )->fetchOne();
    }
}
