<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\GetProductModelAttributesMaskQueryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRequiredProductModelAttributesMaskQuery implements GetProductModelAttributesMaskQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function execute(ProductModelId $productModelId): ?RequiredAttributesMask
    {
        $sql = <<<SQL
WITH
table_column_exists AS (
    SELECT EXISTS (
       SELECT * FROM information_schema.tables
       WHERE table_name = 'pim_catalog_table_column'
    )
)
SELECT
    family.code AS family_code,
    channel_code,
    locale_code,
    JSON_ARRAYAGG(
        CONCAT(
            CASE
                WHEN attribute.attribute_type = 'pim_catalog_price_collection' 
                    THEN CONCAT(
                            attribute.code,
                            '-',
                            (
                                SELECT GROUP_CONCAT(currency.code ORDER BY currency.code SEPARATOR '-')
                                FROM pim_catalog_channel channel
                                JOIN pim_catalog_channel_currency pccc ON channel.id = pccc.channel_id
                                JOIN pim_catalog_currency currency ON pccc.currency_id = currency.id
                                WHERE channel.code  = channel_code
                                GROUP BY channel.id
                            )
                        )
                WHEN attribute.attribute_type = 'pim_catalog_table' 
                    THEN CONCAT(
                            attribute.code,
                            '-',
                            (
                                SELECT GROUP_CONCAT(table_column.id ORDER BY table_column.id SEPARATOR '-')
                                FROM pim_catalog_table_column table_column
                                WHERE (table_column.is_required_for_completeness = '1' OR table_column.column_order = 0)
                                AND table_column.attribute_id = attribute.id
                            )
                        )
                ELSE attribute.code
            END,
            '-',
            IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
            '-',
            IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
        )
    ) AS mask
FROM pim_catalog_product_model AS product_model
INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.id = product_model.family_variant_id
INNER JOIN pim_catalog_family AS family ON family.id = family_variant.family_id
JOIN pim_catalog_attribute_requirement pcar ON family.id = pcar.family_id
JOIN (
    SELECT
        channel.id AS channel_id,
        channel.code AS channel_code,
        locale.id AS locale_id,
        locale.code AS locale_code
    FROM pim_catalog_channel channel
    JOIN pim_catalog_channel_locale AS pccl ON channel.id = pccl.channel_id
    JOIN pim_catalog_locale AS locale ON pccl.locale_id = locale.id
) AS channel_locale ON channel_locale.channel_id = pcar.channel_id
INNER JOIN pim_catalog_attribute AS attribute ON pcar.attribute_id = attribute.id
LEFT JOIN pim_catalog_attribute_locale AS pcal ON attribute.id = pcal.attribute_id
LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
LEFT JOIN pim_data_quality_insights_attribute_group_activation AS attribute_group_activation ON attribute_group_activation.attribute_group_code = attribute_group.code
WHERE product_model.id = :productModelId
    AND pcar.required is true
    AND (pcal.locale_id IS NULL OR pcal.locale_id = channel_locale.locale_id)
    AND (attribute_group_activation.activated IS NULL OR attribute_group_activation.activated = 1)
    AND NOT EXISTS(
        SELECT 1
        FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes
        INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set ON attribute_set.id = attribute_set_attributes.variant_attribute_set_id
        INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.variant_attribute_sets_id = attribute_set.id
        WHERE attribute_set_attributes.attributes_id = attribute.id
          AND family_attribute_set.family_variant_id = family_variant.id
          AND (product_model.parent_id IS NULL OR attribute_set.level = 2)
    )
GROUP BY family.code, channel_code, locale_code;
SQL;
        $rows = $this->dbConnection->executeQuery(
            $sql,
            ['productModelId' => $productModelId->toInt()],
            ['productModelId' => \PDO::PARAM_INT]
        )->fetchAllAssociative();

        if (empty($rows)) {
            return null;
        }

        $masksPerChannelAndLocale = array_map(function (array $row) {
            return new RequiredAttributesMaskForChannelAndLocale(
                $row['channel_code'],
                $row['locale_code'],
                json_decode($row['mask'], true)
            );
        }, $rows);

        return new RequiredAttributesMask($rows[0]['family_code'], $masksPerChannelAndLocale);
    }
}
