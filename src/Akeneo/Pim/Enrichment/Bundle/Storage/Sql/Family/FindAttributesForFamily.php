<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;

/**
 * Checks if an attribute is part of the family attributes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAttributesForFamily
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[]
     */
    public function execute(FamilyInterface $family): array
    {
        $sql = <<<SQL
        SELECT a.code
        FROM pim_catalog_family f
          INNER JOIN pim_catalog_family_attribute fa ON f.id = fa.family_id
          INNER JOIN pim_catalog_attribute a ON fa.attribute_id = a.id
        WHERE (f.code = :family_code)
SQL;

        return $this->getAttributeCodes($this->connection->executeQuery($sql, ['family_code' => $family->getCode()]));
    }

    /**
     * @return string[]
     */
    private function getAttributeCodes(Result $result): array
    {
        return array_map(function (array $result) {
            return $result['code'];
        }, $result->fetchAllAssociative());
    }
}
