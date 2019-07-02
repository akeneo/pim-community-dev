<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
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

    public function save(ProductCompletenessCollection $completenesses): void
    {
        $this->connection->beginTransaction();

        $productId = $completenesses->productId();
        $this->connection->executeQuery($this->getDeleteQuery(), ['productId' => $productId]);

        foreach ($completenesses as $completeness) {
            $this->connection->executeUpdate(
                $this->getInsertCompletenessQuery(),
                [
                    'productId' => $productId,
                    'ratio' => $completeness->ratio(),
                    'missingCount' => count($completeness->missingAttributeCodes()),
                    'requiredCount' => $completeness->requiredCount(),
                    'localeCode' => $completeness->localeCode(),
                    'channelCode' => $completeness->channelCode(),
                ]
            );
            $completenessId = $this->connection->lastInsertId();
            $this->connection->executeUpdate(
                $this->getInsertMissingAttributesQuery(),
                [
                    'completenessId' => $completenessId,
                    'attributeCodes' => $completeness->missingAttributeCodes(),
                ],
                [
                    'attributeCodes' => Connection::PARAM_STR_ARRAY,
                ]
            );
        }

        $this->connection->commit();
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
INSERT INTO pim_catalog_completeness(locale_id, channel_id, product_id, ratio, missing_count, required_count)
SELECT locale.id, channel.id, :productId, :ratio, :missingCount, :requiredCount  
FROM pim_catalog_locale locale,
     pim_catalog_channel channel
WHERE locale.code = :localeCode
  AND channel.code = :channelCode
SQL;
    }

    private function getInsertMissingAttributesQuery(): string
    {
        return <<<SQL
INSERT INTO pim_catalog_completeness_missing_attribute(completeness_id, missing_attribute_id)
SELECT :completenessId, attribute.id
FROM pim_catalog_attribute attribute
WHERE attribute.code IN (:attributeCodes)
SQL;
    }
}
