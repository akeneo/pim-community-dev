<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Infrastructure\Query\Sql;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Category\Domain\Query\FindCategoryCodes;
use Akeneo\Pim\Enrichment\Category\Domain\Query\CategoryQuery;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindCategoryCodes implements FindCategoryCodes
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromQuery(CategoryQuery $query): array
    {
        $params = [];
        $types = [];

        $sqlOffset = '';
        if ($query->page !== null) {
            $sqlOffset = 'OFFSET :offset';
            $params['offset'] = $query->limit * ($query->page - 1);
            $types['offset'] = \PDO::PARAM_INT;
        }

        $sqlLimit = '';
        if ($query->limit !== null) {
            $sqlLimit = 'LIMIT :limit';
            $params['limit'] = $query->limit;
            $types['limit'] = \PDO::PARAM_INT;
        }

        //$where[] = $query->updatedAt !== null ? 'updated > ::updated_at' : '';
        //$where[] = $query->onlyRoot !== null ? 'parent.code is null' : '';
        //$where[] = $query->parentCategory !== null ? 'parent.code = :parent_code' : '';
        if (!empty($query->categoryCodes)) {
            $where[] = 'code IN (:codes)';
            $params['codes'] = $query->categoryCodes;
            $types['codes'] = Connection::PARAM_STR_ARRAY;
        }
        $sqlWhere = !empty($where) ? 'WHERE ' . implode('AND ', $where) : '';

        //$orderBy = [];
        //foreach ($query->orderBy as $property => $order) {
        //    $orderBy[] = "$property $order";
        //}
        //$sqlOrderBy = !empty($orderBy) ? 'ORDER BY' . implode(',', $orderBy) : '';

        $sqlQuery = <<<SQL
            SELECT code
            FROM pim_catalog_category
            $sqlWhere
            $sqlLimit
            $sqlOffset
        SQL;

        return $this->connection->executeQuery(
            $sqlQuery,
            $params,
            $types
        )->fetchFirstColumn();
    }
}
