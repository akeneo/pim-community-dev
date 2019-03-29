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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectFamilyAttributeCodesQueryInterface;
use Doctrine\DBAL\Connection;

class SelectFamilyAttributeCodesQuery implements SelectFamilyAttributeCodesQueryInterface
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
     * {@inheritdoc}
     */
    public function execute(FamilyCode $familyCode): array
    {
        $query = <<<SQL
SELECT attribute.code as attribute_code
FROM pim_catalog_attribute as attribute
INNER JOIN pim_catalog_family_attribute as family_attribute ON attribute.id = family_attribute.attribute_id
INNER JOIN pim_catalog_family as family ON family_attribute.family_id = family.id
WHERE family.code = :family_code
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            ['family_code' => (string) $familyCode],
            ['family_code' => \PDO::PARAM_STR]
        );
        $results = $statement->fetchAll();

        return array_map(
            function ($results) {
                return $results['attribute_code'];
            },
            $results
        );
    }
}
