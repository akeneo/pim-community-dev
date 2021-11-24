<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasksForAttributeType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PriceCollectionSqlGetRequiredAttributesMasks implements GetRequiredAttributesMasksForAttributeType
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
WITH
channel_locale AS (
    SELECT
        channel.id AS channel_id,
        channel.code AS channel_code,
        locale.id AS locale_id,
        locale.code AS locale_code
    FROM pim_catalog_channel channel
        JOIN pim_catalog_channel_locale pccl ON channel.id = pccl.channel_id
        JOIN pim_catalog_locale locale ON pccl.locale_id = locale.id
),
currency_channel AS (
    SELECT
        channel.code AS channel_code,
        GROUP_CONCAT(currency.code ORDER BY currency.code SEPARATOR '-') AS concatenated_currency_codes
    FROM pim_catalog_channel channel
        JOIN pim_catalog_channel_currency pccc ON channel.id = pccc.channel_id
        JOIN pim_catalog_currency currency ON pccc.currency_id = currency.id
    GROUP BY channel.code
)
SELECT
    family.code AS family_code,
    channel_locale.channel_code,
    locale_code,
    JSON_ARRAYAGG(
        CONCAT(
            attribute.code,
            '-',
            currency_channel.concatenated_currency_codes,
            '-',
            IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
            '-',
            IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
        )
    ) AS mask
FROM pim_catalog_family family
    JOIN pim_catalog_attribute_requirement pcar ON family.id = pcar.family_id
    JOIN channel_locale ON channel_locale.channel_id = pcar.channel_id
    JOIN currency_channel ON currency_channel.channel_code = channel_locale.channel_code
    JOIN pim_catalog_attribute attribute ON pcar.attribute_id = attribute.id
    LEFT JOIN pim_catalog_attribute_locale pcal ON attribute.id = pcal.attribute_id
WHERE
    pcar.required is true
    AND (pcal.locale_id IS NULL OR pcal.locale_id = channel_locale.locale_id)
    AND family.code IN (:familyCodes)
    AND attribute.attribute_type = 'pim_catalog_price_collection'
GROUP BY family.code, channel_locale.channel_code, locale_code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            ['familyCodes' => $familyCodes],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $masksPerFamily = [];
        foreach ($rows as $masksPerChannelAndLocale) {
            $masksPerFamily[$masksPerChannelAndLocale['family_code']][] = new RequiredAttributesMaskForChannelAndLocale(
                (string) $masksPerChannelAndLocale['channel_code'],
                (string) $masksPerChannelAndLocale['locale_code'],
                json_decode($masksPerChannelAndLocale['mask'], true)
            );
        }

        $result = [];
        foreach ($masksPerFamily as $familyCode => $masksPerChannelAndLocale) {
            $result[$familyCode] = new RequiredAttributesMask((string) $familyCode, $masksPerChannelAndLocale);
        }

        return $result;
    }
}
