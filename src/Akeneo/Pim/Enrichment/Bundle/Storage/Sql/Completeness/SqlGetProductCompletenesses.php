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
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromProductId(int $productId): ProductCompletenessCollection
    {
        return $this->fromProductIds([$productId])[$productId];
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductIds(array $productIds, ?string $channel = null, array $locales = []): array
    {
        $andWhere = '';
        $params = ['productIds' => $productIds];
        $types = ['productIds' => Connection::PARAM_INT_ARRAY];
        if (null !== $channel) {
            $andWhere .= 'AND channel.code = :channel ';
            $params['channel'] = $channel;
        }
        if (!empty($locales)) {
            $andWhere .= 'AND locale.code IN (:locales) ';
            $params['locales'] = $locales;
            $types['locales'] = Connection::PARAM_STR_ARRAY;
        }

        $sql = sprintf(
            <<<SQL
SELECT 
       completeness.product_id AS product_id,
       JSON_ARRAYAGG(
           JSON_OBJECT(
               'channel_code', channel.code,
               'locale_code', locale.code,
               'required_count', completeness.required_count,
               'missing_count', completeness.missing_count
           )
       ) AS completenesses
FROM pim_catalog_completeness completeness
    INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
    INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
WHERE completeness.product_id IN (:productIds) %s
GROUP BY product_id
SQL,
            $andWhere
        );
        $rows = $this->connection->executeQuery($sql, $params, $types)->fetchAll();

        $results = array_reduce(
            $rows,
            function (array $normalized, array $row) {
                $productId = (int) $row['product_id'];
                $normalized[$productId] = new ProductCompletenessCollection($productId, array_map(
                    function (array $completeness): ProductCompleteness {
                        return new ProductCompleteness(
                            $completeness['channel_code'],
                            $completeness['locale_code'],
                            (int) $completeness['required_count'],
                            (int) $completeness['missing_count']
                        );
                    },
                    json_decode($row['completenesses'], true)
                ));

                return $normalized;
            },
            []
        );
        $missingIds = array_diff($productIds, array_keys($results));
        if (!empty($missingIds)) {
            foreach ($missingIds as $missingId) {
                $results[$missingId] = new ProductCompletenessCollection($missingId, []);
            }
        }

        return $results;
    }
}
