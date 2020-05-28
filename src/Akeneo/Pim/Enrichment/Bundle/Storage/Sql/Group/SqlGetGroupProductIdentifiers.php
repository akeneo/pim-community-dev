<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Group;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetGroupProductIdentifiers implements GetGroupProductIdentifiers
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @inheritDoc
     */
    public function byGroupId(int $groupId): array
    {
        $query = <<<SQL
SELECT product.identifier 
FROM pim_catalog_group_product AS group_product
INNER JOIN pim_catalog_product AS product ON group_product.product_id = product.id
WHERE group_product.group_id = :groupId
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['groupId' => $groupId],
            ['groupId' => \PDO::PARAM_INT]
        );

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
