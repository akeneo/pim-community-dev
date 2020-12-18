<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryChildrenIdsQuery implements GetCategoryChildrenIdsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(CategoryCode $categoryCode): array
    {
        $query = <<<SQL
SELECT JSON_ARRAYAGG(child.id) as ids
FROM pim_catalog_category parent
JOIN pim_catalog_category child ON child.lft >= parent.lft AND child.lft < parent.rgt AND child.root = parent.root
WHERE parent.code = :category_code;
SQL;

        $statement = $this->connection->executeQuery($query, ['category_code' => $categoryCode]);
        $categoryIds = $statement->fetchColumn();

        if (empty($categoryIds)) {
            throw new \RuntimeException(sprintf('The category %s was not found.', $categoryCode));
        }

        return json_decode($categoryIds);
    }
}
