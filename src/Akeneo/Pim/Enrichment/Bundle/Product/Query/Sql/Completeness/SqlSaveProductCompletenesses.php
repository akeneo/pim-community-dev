<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\CannotSaveProductCompletenessCollectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Doctrine\DBAL\Connection;

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
        $this->connection->beginTransaction();

        $productId = $completenesses->productId();
        $this->connection->executeQuery($this->getDeleteQuery(), ['productId' => $productId]);

        foreach ($completenesses as $completeness) {
            $this->connection->executeUpdate(
                $this->getInsertCompletenessQuery(),
                [
                    'productId' => $productId,
                    'missingCount' => count($completeness->missingAttributeCodes()),
                    'requiredCount' => $completeness->requiredCount(),
                    'localeCode' => $completeness->localeCode(),
                    'channelCode' => $completeness->channelCode(),
                ]
            );
        }

        try {
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw new CannotSaveProductCompletenessCollectionException($productId, $e->getCode(), $e);
        }
    }

    private function getDeleteQuery(): string
    {
        return <<<SQL
DELETE FROM pim_catalog_completeness
WHERE product_id = :productId
SQL;
    }

    private function getInsertCompletenessQuery(): string
    {
        return <<<SQL
INSERT INTO pim_catalog_completeness(locale_id, channel_id, product_id, missing_count, required_count)
SELECT locale.id, channel.id, :productId, :missingCount, :requiredCount  
FROM pim_catalog_locale locale,
     pim_catalog_channel channel
WHERE locale.code = :localeCode
  AND channel.code = :channelCode
SQL;
    }
}
