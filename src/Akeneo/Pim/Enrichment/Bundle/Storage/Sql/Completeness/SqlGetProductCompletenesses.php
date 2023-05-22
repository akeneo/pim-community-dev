<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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

    public function fromProductUuid(UuidInterface $productUuid): ProductCompletenessCollection
    {
        return $this->fromProductUuids([$productUuid])[$productUuid->toString()];
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductUuids(array $productUuids, ?string $channel = null, array $locales = []): array
    {
        $andWhere = '';
        $params = ['productUuids' => \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids)];
        $types = ['productUuids' => Connection::PARAM_STR_ARRAY];
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
WITH product_completeness as (
    SELECT DISTINCT BIN_TO_UUID(product_uuid) AS product_uuid, channel_id, locale_id, missing_count, required_count
    FROM pim_catalog_completeness
    WHERE product_uuid IN (:productUuids) 
)
SELECT product_uuid,
       JSON_ARRAYAGG(
           JSON_OBJECT(
               'channel_code', channel.code,
               'locale_code', locale.code,
               'required_count', product_completeness.required_count,
               'missing_count', product_completeness.missing_count
           )
       ) completenesses
FROM product_completeness
INNER JOIN pim_catalog_channel channel ON product_completeness.channel_id = channel.id
INNER JOIN pim_catalog_locale locale ON product_completeness.locale_id = locale.id
%s
GROUP BY product_uuid;
SQL,
            $andWhere
        );
        $rows = $this->connection->executeQuery($sql, $params, $types)->fetchAllAssociative();

        $results = array_reduce(
            $rows,
            function (array $normalized, array $row) {
                $productUuid = Uuid::fromString($row['product_uuid']);
                $normalized[$productUuid->toString()] = new ProductCompletenessCollection($productUuid, array_map(
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

        $productUuidsAsStrings = \array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $productUuids);
        $missingUuids = array_diff($productUuidsAsStrings, array_keys($results));
        if (!empty($missingUuids)) {
            foreach ($missingUuids as $missingUuid) {
                $results[Uuid::fromString($missingUuid)] = new ProductCompletenessCollection(Uuid::fromString($missingUuid), []);
            }
        }

        return $results;
    }
}
