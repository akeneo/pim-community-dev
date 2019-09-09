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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectSupportedAttributesByFamilyQueryInterface;
use Doctrine\DBAL\Connection;

class SelectSupportedAttributesByFamilyQuery implements SelectSupportedAttributesByFamilyQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(FamilyCode $familyCode): array
    {
        $query = <<<SQL
SELECT attribute.code, attribute.attribute_type
FROM pim_catalog_attribute as attribute
    INNER JOIN pim_catalog_family_attribute as family_attribute ON attribute.id = family_attribute.attribute_id
    INNER JOIN pim_catalog_family as family ON family_attribute.family_id = family.id
WHERE family.code = :family_code
    AND attribute.attribute_type IN (:supported_types)
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'family_code' => (string) $familyCode,
                'supported_types' => array_keys(AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS)
            ],
            [
                'family_code' => \PDO::PARAM_STR,
                'supported_types' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $results = $statement->fetchAll();
        $attributes = [];

        foreach ($results as $result) {
            $attributes[$result['code']] = new Attribute(
                new AttributeCode($result['code']),
                new AttributeType($result['attribute_type'])
            );
        }

        return $attributes;
    }
}
