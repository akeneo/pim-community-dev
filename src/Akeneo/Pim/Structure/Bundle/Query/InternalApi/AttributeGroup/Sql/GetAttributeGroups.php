<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeGroup\Sql;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeGroups
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function all(): array
    {
        $sql = <<<SQL
WITH attribute_groups AS (
    SELECT code, sort_order, locale, label FROM pim_catalog_attribute_group attribute_group
    JOIN pim_catalog_attribute_group_translation attribute_group_translations ON attribute_group.id = attribute_group_translations.foreign_key
)
SELECT code, sort_order, JSON_OBJECTAGG(attribute_groups.locale, attribute_groups.label) as labels FROM attribute_groups
GROUP BY code, sort_order
ORDER BY sort_order;
SQL;

        $attributeGroups = $this->connection->executeQuery($sql)->fetchAllAssociative();

        return array_map(function (array $attributeGroup) {
            $attributeGroup['sort_order'] = (int) $attributeGroup['sort_order'];
            $attributeGroup['labels'] = json_decode($attributeGroup['labels'], true);
            // TODO: check FT and call ServiceAPI
            $attributeGroup['is_dqi_activated'] = true;
            return $attributeGroup;
        }, $attributeGroups);
    }
}
