<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Doctrine\DBAL\Connection;

/**
 * Should not be inside DQI, probably a query "GetChildrenCategoryCode" or even a Category Query Builder.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCategoryChildrenCodesQuery implements GetCategoryChildrenCodesQueryInterface
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
SELECT child.code
FROM pim_catalog_category parent
JOIN pim_catalog_category child ON child.lft >= parent.lft AND child.lft < parent.rgt AND child.root = parent.root
WHERE parent.code = :category_code;
SQL;

        $statement = $this->connection->executeQuery($query, ['category_code' => $categoryCode]);
        $categoryCodes = $statement->fetchFirstColumn();

        if (empty($categoryCodes)) {
            throw new \RuntimeException(sprintf('The category %s was not found.', $categoryCode));
        }

        return $categoryCodes;
    }
}
