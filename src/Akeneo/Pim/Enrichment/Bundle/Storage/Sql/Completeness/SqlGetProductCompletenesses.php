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
    private array $channelCodes = [];
    private array $localeCodes = [];

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
        $this->initializeChannelAndLocales();

        $andWhere = '';
        $params = ['productUuids' => \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids)];
        $types = ['productUuids' => Connection::PARAM_STR_ARRAY];
        if (null !== $channel) {
            $andWhere .= 'AND channel_id = (SELECT id FROM pim_catalog_channel WHERE code = :channel) ';
            $params['channel'] = $channel;
        }
        if (!empty($locales)) {
            $andWhere .= 'AND locale_id IN (SELECT id FROM pim_catalog_locale WHERE code IN (:locales)) ';
            $params['locales'] = $locales;
            $types['locales'] = Connection::PARAM_STR_ARRAY;
        }

        $sql = sprintf(
            <<<SQL
            SELECT 
                   BIN_TO_UUID(product.uuid) AS product_uuid,
                   JSON_ARRAYAGG(
                       JSON_OBJECT(
                           'channel_id', channel_id,
                           'locale_id', locale_id,
                           'required_count', completeness.required_count,
                           'missing_count', completeness.missing_count
                       )
                   ) AS completenesses
            FROM pim_catalog_completeness completeness
                INNER JOIN pim_catalog_product product ON product.uuid = completeness.product_uuid    
            WHERE product.uuid IN (:productUuids) %s
            GROUP BY product.uuid
            SQL,
            $andWhere
        );
        $rows = $this->connection->executeQuery($sql, $params, $types)->fetchAllKeyValue();

        $results = [];
        foreach ($rows as $productUuid => $aggCompletenesses) {
            $completenesses = \array_map(
                fn (array $completeness): ProductCompleteness => new ProductCompleteness(
                    $this->channelCodes[$completeness['channel_id']],
                    $this->localeCodes[$completeness['locale_id']],
                    $completeness['required_count'],
                    $completeness['missing_count'],
                ),
                \json_decode($aggCompletenesses, true)
            );

            $results[$productUuid] = new ProductCompletenessCollection(Uuid::fromString($productUuid), $completenesses);
        }

        foreach ($productUuids as $productUuid) {
            $results[$productUuid->toString()] ??= new ProductCompletenessCollection($productUuid, []);
        }

        return $results;
    }

    private function initializeChannelAndLocales(): void
    {
        $this->channelCodes = $this->connection->fetchAllKeyValue(
            'SELECT id, code FROM pim_catalog_channel;'
        );
        $this->localeCodes = $this->connection->fetchAllKeyValue(
            'SELECT id, code FROM pim_catalog_locale;'
        );
    }
}
