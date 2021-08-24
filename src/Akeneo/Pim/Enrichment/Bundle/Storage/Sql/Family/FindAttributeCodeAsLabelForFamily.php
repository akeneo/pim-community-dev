<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\FindAttributeCodeAsLabelForFamilyInterface;
use Doctrine\DBAL\Connection;

/**
 * Checks if an attribute is part of the family attributes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAttributeCodeAsLabelForFamily implements FindAttributeCodeAsLabelForFamilyInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(int $id): string
    {
        $sql = <<<SQL
        SELECT a.code
        FROM pim_catalog_family f
          INNER JOIN pim_catalog_attribute a ON f.label_attribute_id = a.id
        WHERE (f.id = :id)
SQL;
        return $this->connection->executeQuery($sql, ['id' => $id])->fetch(\PDO::FETCH_COLUMN);
    }
}
