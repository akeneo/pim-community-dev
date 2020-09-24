<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRequiredAttributesMasksQuery implements GetRequiredAttributesMasks
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    /**
     * {@inheritdoc}
     */
    public function fromFamilyCodes(array $familyCodes): array
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
FROM pim_catalog_family family
JOIN pim_catalog_attribute_requirement pcar ON family.id = pcar.family_id
JOIN (
    SELECT
        channel.id AS channel_id,
        channel.code AS channel_code,
        locale.id AS locale_id,
        locale.code AS locale_code
    FROM pim_catalog_channel channel
    JOIN pim_catalog_channel_locale pccl ON channel.id = pccl.channel_id
    JOIN pim_catalog_locale locale ON pccl.locale_id = locale.id
) AS channel_locale ON channel_locale.channel_id = pcar.channel_id
JOIN pim_catalog_attribute attribute ON pcar.attribute_id = attribute.id
LEFT JOIN pim_catalog_attribute_locale pcal ON attribute.id = pcal.attribute_id
LEFT JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.id = attribute.group_id
LEFT JOIN pim_data_quality_insights_attribute_group_activation AS attribute_group_activation ON attribute_group_activation.attribute_group_code = attribute_group.code
WHERE
    pcar.required is true
    AND (pcal.locale_id IS NULL OR pcal.locale_id = channel_locale.locale_id)
    AND family.code IN (:familyCodes)
    AND (attribute_group_activation.activated IS NULL OR attribute_group_activation.activated = 1)
GROUP BY family.code, channel_code, locale_code
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['familyCodes' => $familyCodes],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $masksPerFamily = array_fill_keys($familyCodes, []);
        foreach ($rows as $masksPerChannelAndLocale) {
            $masksPerFamily[$masksPerChannelAndLocale['family_code']][] = new RequiredAttributesMaskForChannelAndLocale(
                $masksPerChannelAndLocale['channel_code'],
                $masksPerChannelAndLocale['locale_code'],
                json_decode($masksPerChannelAndLocale['mask'], true)
            );
        }

        $masks = [];
        foreach ($masksPerFamily as $familyCode => $masksPerChannelAndLocale) {
            $masks[$familyCode] = new RequiredAttributesMask($familyCode, $masksPerChannelAndLocale);
        }

        return $masks;
    }
}
