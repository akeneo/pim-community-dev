<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCompletenessFamilyMasks
{
    /** @var Connection */
    private $connection;

    /** @var array */
    private $cache;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->cache = [];
    }

    /**
     * @param string[] $familyCodes
     *
     * @return CompletenessFamilyMask[]
     */
    public function fromFamilyCodes(array $familyCodes): array
    {
        $result = [];
        $nonFetchedFamilyCodes = [];
        foreach ($familyCodes as $familyCode) {
            if (isset($this->cache[$familyCode])) {
                $result[$familyCode] = $this->cache[$familyCode];
            } else {
                $nonFetchedFamilyCodes[] = $familyCode;
            }
        }

        $fetched = $this->fetch($nonFetchedFamilyCodes);
        foreach ($fetched as $familyCode => $masks) {
            $this->cache[$familyCode] = $masks;
            $result[$familyCode] = $masks;
        }

        return $result;
    }

    private function fetch(array $familyCodes): array
    {
        $sql = <<<SQL
SELECT 
    family.code AS family_code,
    channel_code,
    locale_code,
    JSON_ARRAYAGG(
        CONCAT(
            attribute.code,
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
WHERE 
    pcar.required is true
    AND (pcal.locale_id IS NULL or pcal.locale_id = channel_locale.locale_id)
    AND family.code IN (:familyCodes)
GROUP BY family.code, channel_code, locale_code
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['familyCodes' => $familyCodes],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $masksPerFamily = [];
        foreach ($rows as $masksPerChannelAndLocale) {
            $masksPerFamily[$masksPerChannelAndLocale['family_code']][] = new CompletenessFamilyMaskPerChannelAndLocale(
                $masksPerChannelAndLocale['channel_code'],
                $masksPerChannelAndLocale['locale_code'],
                json_decode($masksPerChannelAndLocale['mask'], true)
            );
        }

//        var_dump($familyCodes);
        $result = [];
        foreach ($masksPerFamily as $familyCode => $masksPerChannelAndLocale) {
            $result[$familyCode] = new CompletenessFamilyMask(
                $familyCode,
                $masksPerChannelAndLocale
            );
        }

        return $result;
    }
}
