<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetProductCompletenesses implements GetProductCompletenesses
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductId(int $productId): array
    {
        $sql = <<<SQL
SELECT 
       channel.code AS channel_code,
       locale.code AS locale_code,
       completeness.required_count AS required_count,
       JSON_ARRAYAGG(attribute.code) AS missing_attribute_codes
FROM pim_catalog_completeness completeness
    INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
    INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
    LEFT JOIN pim_catalog_completeness_missing_attribute missing_attributes on completeness.id = missing_attributes.completeness_id
    LEFT JOIN pim_catalog_attribute attribute ON attribute.id = missing_attributes.missing_attribute_id
WHERE completeness.product_id = :productId
GROUP BY completeness.required_count, channel.code, locale.code
SQL;
        $rows = $this->connection->executeQuery($sql, ['productId' => $productId])->fetchAll();

        return array_map(
            function (array $row): ProductCompleteness {
                return new ProductCompleteness(
                    $row['channel_code'],
                    $row['locale_code'],
                    (int)$row['required_count'],
                    array_filter(json_decode($row['missing_attribute_codes']))
                );
            },
            $rows
        );
    }
}
