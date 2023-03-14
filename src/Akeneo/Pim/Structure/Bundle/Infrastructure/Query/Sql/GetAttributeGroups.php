<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Infrastructure\Query\Sql;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\AttributeGroup\GetAttributeGroupsActivationQueryInterface;
use Akeneo\Pim\Structure\Bundle\Domain\Query\Sql\GetAttributeGroupsInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeGroups implements GetAttributeGroupsInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly FeatureFlags $featureFlags,
        private readonly GetAttributeGroupsActivationQueryInterface $getAttributeGroupsActivationQuery,
    ) {
    }

    public function all(): iterable
    {
        $sql = <<<SQL
            WITH attribute_group_labels AS (
                SELECT foreign_key AS attribute_group_id, JSON_OBJECTAGG(locale, label) AS labels
                FROM `pim_catalog_attribute_group_translation`
                GROUP BY foreign_key
            ),
            attribute_group_count AS (
                SELECT group_id, COUNT(id) AS attribute_count
                FROM `pim_catalog_attribute`
                GROUP BY group_id
            )
            SELECT attribute_group.code, attribute_group.sort_order, labels, attribute_count
            FROM `pim_catalog_attribute_group` attribute_group
            LEFT JOIN `attribute_group_labels` ON attribute_group_labels.attribute_group_id = attribute_group.id
            LEFT JOIN `attribute_group_count` ON attribute_group_count.group_id = attribute_group.id
            ORDER BY sort_order;
        SQL;

        $attributeGroups = $this->connection->executeQuery($sql)->fetchAllAssociative();
        $dqiFeatureIsEnabled = $this->featureFlags->isEnabled('data_quality_insights');
        $dqiAttributeGroupsActivation = $this->getAttributeGroupsActivationQuery->all();

        return array_map(function (array $attributeGroup) use ($dqiFeatureIsEnabled, $dqiAttributeGroupsActivation) {
            $attributeGroup['sort_order'] = (int) $attributeGroup['sort_order'];
            $attributeGroup['labels'] = null !== $attributeGroup['labels'] ? json_decode($attributeGroup['labels'], true) : [];
            if ($dqiFeatureIsEnabled) {
                $attributeGroup['is_dqi_activated'] = $dqiAttributeGroupsActivation->isActivated($attributeGroup['code']);
            }
            $attributeGroup['attribute_count'] = (int) $attributeGroup['attribute_count'] ?? 0;
            return $attributeGroup;
        }, $attributeGroups);
    }
}
