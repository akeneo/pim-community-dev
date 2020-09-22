<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateHeadersFromAttributeCodes implements GenerateFlatHeadersFromAttributeCodesInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Generate all possible headers from the provided attribute codes
     *
     * @return FlatFileHeader[]
     */
    public function __invoke(
        array $attributeCodes,
        string $channelCode,
        array $localeCodes
    ): array {
        $activatedCurrencyCodes = $this->connection->executeQuery(
            "SELECT code FROM pim_catalog_currency WHERE is_activated = 1"
        )->fetchAll(\PDO::FETCH_COLUMN, 0);

        $channelCurrencyCodesSql = <<<SQL
            SELECT currency.code
            FROM pim_catalog_channel channel
              JOIN pim_catalog_channel_currency cc ON cc.channel_id = channel.id
              JOIN pim_catalog_currency currency ON currency.id = cc.currency_id
            WHERE channel.code = :channelCode
SQL;
        $channelCurrencyCodes = $this->connection->executeQuery(
            $channelCurrencyCodesSql,
            ['channelCode' => $channelCode]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);

        $attributesDataSql = <<<SQL
            WITH attribute_specific_to_locales as (
                 SELECT attribute_id, JSON_ARRAYAGG(l.code) AS specific_to_locales
                 FROM pim_catalog_locale l
                 JOIN pim_catalog_attribute_locale al ON al.locale_id = l.id
                 GROUP BY al.attribute_id
            )

            SELECT a.code,
                   a.is_scopable,
                   a.is_localizable,
                   a.attribute_type,
                   astl.specific_to_locales
            FROM pim_catalog_attribute a
            LEFT JOIN attribute_specific_to_locales astl ON astl.attribute_id = a.id
            WHERE a.code IN (:attributeCodes)
            GROUP BY a.id;
SQL;

        $attributesData = $this->connection->executeQuery(
            $attributesDataSql,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $headers = [];
        foreach ($attributesData as $attributeData) {
            $headers[] = FlatFileHeader::buildFromAttributeData(
                $attributeData["code"],
                $attributeData["attribute_type"],
                ("1" === $attributeData["is_scopable"]),
                $channelCode,
                ("1" === $attributeData["is_localizable"]),
                $localeCodes,
                $channelCurrencyCodes,
                $activatedCurrencyCodes,
                null !== $attributeData['specific_to_locales'] ? json_decode($attributeData['specific_to_locales'], true) : []
            );
        }

        return $headers;
    }
}
