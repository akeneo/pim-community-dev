<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Family\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasksForAttributeType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Doctrine\DBAL\Connection;

final class TableSqlGetRequiredAttributesMasks implements GetRequiredAttributesMasksForAttributeType
{
    private Connection $connection;

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
required_table_attribute_in_family AS (
    SELECT
        family.id AS family_id,
        pcar.attribute_id,
        pcar.channel_id
    FROM pim_catalog_family family
        JOIN pim_catalog_attribute_requirement pcar ON family.id = pcar.family_id
        JOIN pim_catalog_attribute attribute ON attribute.id = pcar.attribute_id
    WHERE family.code IN (:familyCodes) 
        AND attribute.attribute_type = 'pim_catalog_table'
        AND pcar.required is true
),
required_table_column AS (
    SELECT
        required_table_attribute_in_family.attribute_id,
        required_table_attribute_in_family.channel_id,
        GROUP_CONCAT(table_column.id ORDER BY table_column.id SEPARATOR '-') AS concatenated_column_ids
    FROM required_table_attribute_in_family
        JOIN pim_catalog_table_column table_column ON table_column.attribute_id = required_table_attribute_in_family.attribute_id
    WHERE table_column.is_required_for_completeness = '1' OR table_column.column_order = 0
    GROUP BY required_table_attribute_in_family.attribute_id, required_table_attribute_in_family.channel_id
)
SELECT
    family.code AS family_code,
    channel_locale.channel_code,
    channel_locale.locale_code,
    JSON_ARRAYAGG(
        CONCAT(
            attribute.code,
            '-',
            required_table_column.concatenated_column_ids,
            '-',
            IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
            '-',
            IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
        )
    ) AS mask
FROM required_table_attribute_in_family rtaif
    JOIN pim_catalog_family family ON family.id = rtaif.family_id
    JOIN channel_locale ON channel_locale.channel_id = rtaif.channel_id
    JOIN pim_catalog_attribute attribute ON rtaif.attribute_id = attribute.id
    JOIN required_table_column ON required_table_column.attribute_id = rtaif.attribute_id
                              AND required_table_column.channel_id = channel_locale.channel_id
    LEFT JOIN pim_catalog_attribute_locale pcal ON attribute.id = pcal.attribute_id
WHERE
    (pcal.locale_id IS NULL OR pcal.locale_id = channel_locale.locale_id)
GROUP BY family.code, channel_code, locale_code
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
                \json_decode($masksPerChannelAndLocale['mask'], true)
            );
        }

        $result = [];
        foreach ($masksPerFamily as $familyCode => $masksPerChannelAndLocale) {
            $result[$familyCode] = new RequiredAttributesMask((string) $familyCode, $masksPerChannelAndLocale);
        }

        return $result;
    }
}
