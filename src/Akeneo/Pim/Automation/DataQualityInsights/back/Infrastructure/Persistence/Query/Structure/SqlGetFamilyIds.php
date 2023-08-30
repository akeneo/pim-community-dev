<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamilyIds;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Doctrine\DBAL\Connection;

final class SqlGetFamilyIds implements GetFamilyIds
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fromActivatedAttributeIds(array $attributeIds): \Generator
    {
        if ([] === $attributeIds) {
            return;
        }

        $query = <<<SQL
SELECT DISTINCT family_attribute.family_id
FROM pim_catalog_family_attribute AS family_attribute
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = family_attribute.attribute_id
    LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
    LEFT JOIN pim_data_quality_insights_attribute_group_activation AS activation ON activation.attribute_group_code = attribute_group.code
WHERE family_attribute.attribute_id IN (:attribute_ids)
     AND (activation.activated IS NULL OR activation.activated = 1);
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['attribute_ids' => $attributeIds],
            ['attribute_ids' => Connection::PARAM_INT_ARRAY]
        );

        while ($familyId = $stmt->fetchOne()) {
            yield new FamilyId((int) $familyId);
        }
    }

    public function fromAttributeGroupCode(AttributeGroupCode $attributeGroupCode): \Generator
    {
        $query = <<<SQL
SELECT DISTINCT family_attribute.family_id
FROM pim_catalog_family_attribute AS family_attribute
    INNER JOIN pim_catalog_attribute attribute ON attribute.id = family_attribute.attribute_id
    INNER JOIN pim_catalog_attribute_group attr_group ON attr_group.id = attribute.group_id
WHERE attr_group.code = :attribute_group_code;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['attribute_group_code' => $attributeGroupCode]
        );

        while ($familyId = $stmt->fetchOne()) {
            yield new FamilyId((int) $familyId);
        }
    }
}
