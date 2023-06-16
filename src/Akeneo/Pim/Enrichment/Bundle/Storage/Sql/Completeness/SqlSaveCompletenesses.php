<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

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
        $params = \implode(', ', \array_fill(0, count($productCompletenessCollections), '(?, ?)'));
        $query = \sprintf(
            <<<SQL
            INSERT INTO pim_catalog_product_completeness (product_uuid, completeness) 
                  VALUES %s
                  ON DUPLICATE KEY UPDATE completeness = VALUES(completeness)
            SQL,
            $params
        );

        $statement = $this->connection->prepare($query);
        $placeholderIndex = 0;
        foreach ($productCompletenessCollections as $productCompletenessCollection) {
            $uuid = Uuid::fromString($productCompletenessCollection->productId())->getBytes();
            $completenessValues = [];
            foreach ($productCompletenessCollection as $completeness) {
                $completenessValues[$completeness->channelCode()][$completeness->localeCode()] = [
                    'required' => $completeness->requiredCount(),
                    'missing' => $completeness->missingAttributesCount(),
                ];
            }
            $statement->bindValue(++$placeholderIndex, $uuid, Types::BINARY);
            $statement->bindValue(++$placeholderIndex, \json_encode($completenessValues));
        }

        $statement->executeStatement();
    }
}
