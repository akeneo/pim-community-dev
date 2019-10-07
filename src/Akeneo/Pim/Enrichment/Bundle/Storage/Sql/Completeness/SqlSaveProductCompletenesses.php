<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
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
     * We use INSERT statements with multiple VALUES lists to insert several rows at a time. Performance is 7 times better
     * on the icecat catalog this way, instead of using separate single-row INSERT statements.
     *
     *
     * @see https://dev.mysql.com/doc/refman/5.7/en/insert-optimization.html
     *
     * {@inheritdoc}
     */
    public function saveAll(array $productCompletenessCollections): void
    {
        $this->connection->transactional(function (Connection $connection) use ($productCompletenessCollections) {
            $productIds = array_unique(array_map(function (ProductCompletenessWithMissingAttributeCodesCollection $productCompletenessCollection) {
                return $productCompletenessCollection->productId();
            }, $productCompletenessCollections));


            $localeIdsFromCode = $this->localeIdsIndexedByLocaleCodes();
            $channelIdsFromCode = $this->channelIdsIndexedByChannelCodes();

            $connection->executeQuery(
                'DELETE FROM pim_catalog_completeness WHERE product_id IN (:product_ids)',
                ['product_ids' => $productIds],
                ['product_ids' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
            );

            $numberCompletenessRow = 0;
            foreach ($productCompletenessCollections as $productCompletenessCollection) {
                $numberCompletenessRow += count($productCompletenessCollection);
            }
            $placeholders = implode(',', array_fill(0, $numberCompletenessRow, '(?, ?, ?, ?, ?)'));

            if (empty($placeholders)) {
                return;
            }

            $insert = <<<SQL
                INSERT INTO pim_catalog_completeness
                    (locale_id, channel_id, product_id, missing_count, required_count)
                VALUES
                    $placeholders
SQL;

            $stmt = $this->connection->prepare($insert);

            $placeholderIndex = 1;
            foreach ($productCompletenessCollections as $productCompletenessCollection) {
                foreach ($productCompletenessCollection as $productCompleteness) {
                    $stmt ->bindValue($placeholderIndex++, $localeIdsFromCode[$productCompleteness->localeCode()]);
                    $stmt ->bindValue($placeholderIndex++, $channelIdsFromCode[$productCompleteness->channelCode()]);
                    $stmt ->bindValue($placeholderIndex++, $productCompletenessCollection->productId(), ParameterType::INTEGER);
                    $stmt ->bindValue($placeholderIndex++, count($productCompleteness->missingAttributeCodes()), ParameterType::INTEGER);
                    $stmt ->bindValue($placeholderIndex++, $productCompleteness->requiredCount(), ParameterType::INTEGER);
                }
            }

            $stmt->execute();
        });
    }

    private function localeIdsIndexedByLocaleCodes(): array
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
