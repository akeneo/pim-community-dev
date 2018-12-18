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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\FamilyAttribute;

use Akeneo\Pim\Automation\SuggestData\Domain\FamilyAttribute\Query\FindFamilyAttributesNotInQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FindFamilyAttributesNotInQuery implements FindFamilyAttributesNotInQueryInterface
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
    public function findFamilyAttributesNotIn(string $familyCode, array $attributeCodes): array
    {
        $query = <<<SQL
SELECT attribute.code as family_attribute_code
FROM pim_catalog_family_attribute as family_attributes

INNER JOIN pim_catalog_family as family
  ON family_attributes.family_id = family.id

INNER JOIN pim_catalog_attribute as attribute
  ON family_attributes.attribute_id = attribute.id

WHERE family.code = :family_code AND attribute.code NOT IN (:attribute_codes)
SQL;

        $bindValues = ['family_code' => $familyCode, 'attribute_codes' => $attributeCodes];
        $bindTypes = ['family_code' => \PDO::PARAM_STR, 'attribute_codes' => Connection::PARAM_STR_ARRAY];
        $statement = $this->connection->executeQuery($query, $bindValues, $bindTypes);
        $results = $statement->fetchAll();

        $removedAttributes = array_map(
            function ($results) {
                return $results['family_attribute_code'];
            },
            $results
        );

        return $removedAttributes;
    }
}
