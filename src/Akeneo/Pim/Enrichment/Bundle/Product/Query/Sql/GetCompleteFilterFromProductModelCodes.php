<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Doctrine\DBAL\Connection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetCompleteFilterFromProductModelCodes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchByProductModelCodes(array $productModelCodes): array
    {
        $query = <<<SQL
WITH products AS (
    SELECT
        product_model.code AS code,
        locale.code AS locale_code,
        channel.code AS channel_code,
        JSON_ARRAYAGG(completeness.missing_count),
        SUM(completeness.missing_count) = 0 AS all_complete,
        MIN(completeness.missing_count) <> 0 AS all_incomplete
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product product ON product.product_model_id = product_model.id
    INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
    INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
    INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
    WHERE product_model.parent_id IS NULL
    AND product_model.code IN (:productModelCodes)
    GROUP BY product_model.code, locale_code, channel_code
UNION
    SELECT
        product_model.code AS code,
        locale.code AS locale_code,
        channel.code AS channel_code,
        JSON_ARRAYAGG(completeness.missing_count),
        SUM(completeness.missing_count) = 0 AS allcomplete,
        MIN(completeness.missing_count) <> 0 AS allincomplete
    FROM pim_catalog_product_model product_model
    INNER JOIN pim_catalog_product_model root_product_model ON product_model.parent_id = root_product_model.id
    INNER JOIN pim_catalog_product product ON product.product_model_id = product_model.id
    INNER JOIN pim_catalog_completeness completeness ON product.id = completeness.product_id
    INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
    INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
    WHERE root_product_model.code IN (:productModelCodes)
    GROUP BY product_model.code, locale_code, channel_code
), products_per_channel AS (
    SELECT
         code,
         channel_code,
         JSON_OBJECTAGG(locale_code, all_complete) AS all_complete,
         JSON_OBJECTAGG(locale_code, all_incomplete) AS all_incomplete
    FROM products
    GROUP BY code, channel_code
)
SELECT
    code,
    JSON_OBJECTAGG(channel_code, all_complete) AS all_complete,
    JSON_OBJECTAGG(channel_code, all_incomplete) AS all_incomplete
FROM products_per_channel
GROUP BY code
SQL;

        $rows = $this->connection->fetchAll(
            $query,
            ['productModelCodes' => $productModelCodes],
            ['productModelCodes' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        foreach ($productModelCodes as $productModelCode) {
            $results[$productModelCode] = [
                'all_complete' => [],
                'all_incomplete' => [],
            ];
        }
        foreach ($rows as $row) {
            $results[$row['code']] = [
                'all_complete' => json_decode($row['all_complete'], true),
                'all_incomplete' => json_decode($row['all_incomplete'], true),
            ];
        }

        return $results;
    }
}
