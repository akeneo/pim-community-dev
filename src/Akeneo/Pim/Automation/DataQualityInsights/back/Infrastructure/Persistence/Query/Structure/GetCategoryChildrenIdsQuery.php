<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetCategoryChildrenIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Doctrine\DBAL\Connection;

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
