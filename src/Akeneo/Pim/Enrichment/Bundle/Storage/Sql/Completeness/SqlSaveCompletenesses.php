<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlSaveCompletenesses implements SaveProductCompletenesses
{
    public function __construct(
        private Connection $connection,
    ) {
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
        foreach ($productCompletenessCollections as $productCompleteness) {
            $completenessValues = [];
            $arrayProductCompleteness = \array_values($productCompleteness->getIterator()->getArrayCopy());

            foreach ($arrayProductCompleteness as $completeness) {
                $completenessValues[$completeness->channelCode()][$completeness->localeCode()] = [
                    'required' => $completeness->requiredCount(),
                    'missing' => $completeness->missingAttributesCount(),
                ];
            }

            $query = <<<SQL
            INSERT INTO pim_catalog_product_completeness (product_uuid, completeness) 
                  VALUES (UUID_TO_BIN(:uuid), :completeness) 
                  ON DUPLICATE KEY UPDATE completeness = VALUES(completeness)
            SQL;

            $this->connection->executeStatement($query, [
                'uuid' => $productCompleteness->productId(),
                'completeness' => \json_encode($completenessValues),
            ]);
        }
    }
}
