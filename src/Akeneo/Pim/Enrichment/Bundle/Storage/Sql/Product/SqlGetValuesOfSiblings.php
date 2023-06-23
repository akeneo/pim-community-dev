<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetValuesOfSiblings;
use Doctrine\DBAL\Connection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetValuesOfSiblings implements GetValuesOfSiblings
{
    /** @var Connection  */
    private $connection;

    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    public function __construct(Connection $connection, WriteValueCollectionFactory $valueCollectionFactory)
    {
        $this->connection = $connection;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    public function for(EntityWithFamilyVariantInterface $entity, array $attributeCodesToFilter = []): array
    {
        if (null === $entity->getParent()) {
            return [];
        }

        if ($entity instanceof ProductModelInterface) {
            $identifier = $entity->getCode();
            $sql = <<<SQL
SELECT code as identifier, raw_values
FROM pim_catalog_product_model
WHERE parent_id = :parentId
AND code != :identifier;
SQL;
        } elseif ($entity instanceof ProductInterface) {
            $identifier = $entity->getUuid()->toString();
            $sql = <<<SQL
SELECT pim_catalog_product_unique_data.raw_data AS identifier, BIN_TO_UUID(uuid) AS uuid, raw_values
FROM pim_catalog_product
INNER JOIN pim_catalog_product_unique_data ON pim_catalog_product_unique_data.product_uuid = pim_catalog_product.uuid
INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id = pim_catalog_product_unique_data.attribute_id
WHERE product_model_id = :parentId
AND pim_catalog_attribute.main_identifier = 1
AND uuid != UUID_TO_BIN(:identifier)
SQL;
        } else {
            return [];
        }

        $valuesOfSiblings = [];
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'parentId' => $entity->getParent()->getId(),
                'identifier' => $identifier,
            ]
        );

        foreach ($rows as $row) {
            $rawValues = json_decode($row['raw_values'], true) ?? [];

            if (!empty($attributeCodesToFilter)) {
                $rawValues = array_filter($rawValues, function (string $attributeCode) use ($attributeCodesToFilter) {
                    return in_array($attributeCode, $attributeCodesToFilter);
                }, ARRAY_FILTER_USE_KEY);
            }

            $valuesOfSiblings[$row['identifier'] ?? $row['uuid']] = $this->valueCollectionFactory->createFromStorageFormat(
                $rawValues
            );
        }

        return $valuesOfSiblings;
    }
}
