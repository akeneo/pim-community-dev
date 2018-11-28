<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SelectFamilyCodesByAttributeQuery implements SelectFamilyCodesByAttributeQueryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $attributeCode
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    public function execute(string $attributeCode): array
    {
        $sql = <<<SQL
SELECT pim_catalog_family.code
FROM pim_catalog_family
INNER JOIN pim_catalog_family_attribute pcfa ON pim_catalog_family.id = pcfa.family_id
INNER JOIN pim_catalog_attribute a ON pcfa.attribute_id = a.id
WHERE a.code = :attributeCode;
SQL;

        $bindParams = [
            'attributeCode' => $attributeCode,
        ];

        $statement = $this->connection->executeQuery($sql, $bindParams);

        $result = $statement->fetchAll();

        $familyCodes = [];
        foreach ($result as $row) {
            $familyCodes[] = $row['code'];
        }

        return $familyCodes;
    }
}
