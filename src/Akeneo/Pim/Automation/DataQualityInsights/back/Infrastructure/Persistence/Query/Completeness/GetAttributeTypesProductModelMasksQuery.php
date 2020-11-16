<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\GetProductModelAttributesMaskQueryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Doctrine\DBAL\Connection;

class GetAttributeTypesProductModelMasksQuery implements GetProductModelAttributesMaskQueryInterface
{
    private Connection $connection;

    /** @var string[] */
    private array $attributeTypes;

    public function __construct(Connection $connection, array $attributeTypes)
    {
        $this->connection = $connection;
        $this->attributeTypes = array_map(fn ($code) => (string) $code, $attributeTypes);
    }

    public function execute(ProductId $productModelId): ?RequiredAttributesMask
    {
        $sql = <<<SQL
SELECT
    family.code AS family_code,
    channel_code,
    locale_code,
    JSON_ARRAYAGG(
        CONCAT(
            IF(
                attribute.attribute_type = 'pim_catalog_price_collection',
                CONCAT(
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
                ),
                attribute.code
            ),
            '-',
            IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
            '-',
            IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
        )
    ) AS mask
FROM pim_catalog_product_model AS product_model
INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.id = product_model.family_variant_id
INNER JOIN pim_catalog_family AS family ON family.id = family_variant.family_id
INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.family_id = family.id
INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = family_attribute.attribute_id
LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
LEFT JOIN pim_data_quality_insights_attribute_group_activation AS attribute_group_activation ON attribute_group_activation.attribute_group_code = attribute_group.code
JOIN (
    SELECT
        channel.id AS channel_id,
        channel.code AS channel_code,
        locale.id AS locale_id,
        locale.code AS locale_code
    FROM pim_catalog_channel channel
        JOIN pim_catalog_channel_locale pccl ON channel.id = pccl.channel_id
        JOIN pim_catalog_locale locale ON pccl.locale_id = locale.id
) AS channel_locale
LEFT JOIN pim_catalog_attribute_locale AS pcal ON attribute.id = pcal.attribute_id
LEFT JOIN pim_catalog_attribute_requirement pcar
    ON family.id = pcar.family_id AND attribute.id = pcar.attribute_id AND channel_locale.channel_id = pcar.channel_id
WHERE product_model.id = :productModelId
    AND attribute.attribute_type IN (:attributeTypes)
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
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'productModelId' => $productModelId->toInt(),
                'attributeTypes' => $this->attributeTypes
            ],
            [
                'productModelId' => \PDO::PARAM_INT,
                'attributeTypes' => Connection::PARAM_STR_ARRAY
            ]
        )->fetchAll(\PDO::FETCH_ASSOC);

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
