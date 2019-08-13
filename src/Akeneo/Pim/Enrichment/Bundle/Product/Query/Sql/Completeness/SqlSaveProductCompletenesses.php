<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlSaveProductCompletenesses implements SaveProductCompletenesses
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): void
    {
        $this->saveAll([$completenesses]);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $productCompletenessCollections): void
    {
        $this->connection->transactional(function (Connection $connection) use ($productCompletenessCollections) {
            $productIds = array_unique(array_map(function (ProductCompletenessWithMissingAttributeCodesCollection $productCompletenessCollection) {
                return $productCompletenessCollection->productId();
            }, $productCompletenessCollections));


            $localeIdsFromCode = $this->localeIsdIndexedByLocaleCodes();
            $channelIdsFromCode = $this->channelIdsIndexedByChannelCodes();

            $connection->executeQuery(
                'DELETE FROM pim_catalog_completeness WHERE product_id IN (:product_ids)',
                ['product_ids' => $productIds],
                ['product_ids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
            );

            $insert = <<<SQL
                INSERT INTO pim_catalog_completeness
                    (locale_id, channel_id, product_id, missing_count, required_count, ratio)
                VALUES
                    (:locale_id, :channel_id, :product_id, :missing_count, :required_count, 0)
SQL;
            $stmt = $this->connection->prepare($insert);

            foreach ($productCompletenessCollections as $productCompletenessCollection) {
                foreach ($productCompletenessCollection as $productCompleteness) {
                    $stmt ->bindValue(':locale_id', $localeIdsFromCode[$productCompleteness->localeCode()]);
                    $stmt ->bindValue(':channel_id', $channelIdsFromCode[$productCompleteness->channelCode()]);
                    $stmt ->bindValue(':product_id', $productCompletenessCollection->productId(), ParameterType::INTEGER);
                    $stmt ->bindValue(':missing_count', count($productCompleteness->missingAttributeCodes()), ParameterType::INTEGER);
                    $stmt ->bindValue(':required_count', $productCompleteness->requiredCount(), ParameterType::INTEGER);

                    $stmt->execute();
                }

            }
        });
    }

    /**
     * Return an array of SQL values for pim_catalog_completeness
     *
     * @return array ['(locale_id, channel_id, product_id, missing_count, required_count)', ...]
     */
    private function productCompletenessCollectionsToSqlValues(array $productCompletenessCollections): array
    {
        $localeIdsByCodes = $this->localeIsdIndexedByLocaleCodes();
        $channelIdsByCodes = $this->channelIdsIndexedByChannelCodes();

        $completenessValues = array_map(function (ProductCompletenessCollection $productCompletenessCollection) use ($localeIdsByCodes, $channelIdsByCodes) {
            // return ['(locale_id, channel_id, product_id, missing_count, required_count)', ...];
            return array_map(function (ProductCompleteness $productCompleteness) use ($localeIdsByCodes, $channelIdsByCodes, $productCompletenessCollection) {
                return implode(',', [
                    $localeIdsByCodes[$productCompleteness->localeCode()],
                    $channelIdsByCodes[$productCompleteness->channelCode()],
                    $productCompletenessCollection->productId(),
                    count($productCompleteness->missingAttributeCodes()), //TODO TO REPLACE WITH THE COUNT METHOD
                    $productCompleteness->requiredCount()
                ]);
            }, iterator_to_array($productCompletenessCollection));
        }, $productCompletenessCollections);

        $completenessValues = array_merge(...$completenessValues);

        return implode(',', $completenessValues);
    }

    private function localeIsdIndexedByLocaleCodes(): array
    {
        $query = 'SELECT locale.id as locale_id, locale.code as locale_code FROM pim_catalog_locale locale';

        $rows = $this->connection->fetchAll($query);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['locale_code']] = $row['locale_id'];
        }

        return $result;
    }

    private function channelIdsIndexedByChannelCodes(): array
    {
        $query = 'SELECT channel.id as channel_id, channel.code as channel_code FROM pim_catalog_channel channel';
        $rows = $this->connection->fetchAll($query);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['channel_code']] = $row['channel_id'];
        }

        return $result;
    }
}
