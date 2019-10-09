<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
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

    public function fromProductId(int $productId): ProductCompletenessCollection
    {
        $sql = <<<SQL
SELECT 
       channel.code AS channel_code,
       locale.code AS locale_code,
       completeness.required_count AS required_count,
       completeness.missing_count AS missing_count
FROM pim_catalog_completeness completeness
    INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
    INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
WHERE completeness.product_id = :productId
SQL;
        $rows = $this->connection->executeQuery($sql, ['productId' => $productId])->fetchAll();

        return new ProductCompletenessCollection($productId, array_map(
            function (array $row): ProductCompleteness {
                return new ProductCompleteness(
                    $row['channel_code'],
                    $row['locale_code'],
                    (int)$row['required_count'],
                    (int)$row['missing_count']
                );
            },
            $rows
        ));
    }
}
