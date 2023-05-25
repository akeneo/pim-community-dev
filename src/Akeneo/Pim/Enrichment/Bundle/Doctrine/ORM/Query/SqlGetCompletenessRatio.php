<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCompletenessRatio implements GetProductCompletenessRatio
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function forChannelCodeAndLocaleCode(UuidInterface $productUuid, string $channelCode, string $localeCode): ?int
    {
        $sql = <<<SQL
SELECT FLOOR(((completeness.required - completeness.missing) / completeness.required) * 100) AS ratio
FROM (
    SELECT
        product_uuid,
        channel,
        locale,
        JSON_EXTRACT(completeness, JSON_UNQUOTE(CONCAT('$.', channel, '.', locale, '.missing')))  AS missing,
        JSON_EXTRACT(completeness, JSON_UNQUOTE(CONCAT('$.', channel, '.', locale, '.required'))) AS required
    FROM pim_catalog_product_completeness,
       JSON_TABLE(
               JSON_KEYS(completeness),
               '$[*]' COLUMNS (
                   channel VARCHAR(50) PATH '$'
                   )
           ) AS jt,
       JSON_TABLE(
               JSON_KEYS(JSON_EXTRACT(completeness, CONCAT('$.', channel))),
               '$[*]' COLUMNS (
                   locale VARCHAR(10) PATH '$'
                   )
           ) AS jt2) completeness
WHERE completeness.product_uuid = :uuid
AND completeness.channel = :channel
AND completeness.locale = :locale
SQL;
        $ratio = $this->connection->executeQuery(
            $sql,
            [
                'uuid' => $productUuid->getBytes(),
                'channel' => $channelCode,
                'locale' => $localeCode,
            ]
        )->fetchOne();

        return false === $ratio ? null : (int) $ratio;
    }
}
