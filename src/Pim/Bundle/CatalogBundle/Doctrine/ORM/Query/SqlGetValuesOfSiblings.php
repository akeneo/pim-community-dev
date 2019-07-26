<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\EntityWithFamilyVariant\Query\GetValuesOfSiblings;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetValuesOfSiblings implements GetValuesOfSiblings
{
    /** @var Connection  */
    private $connection;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    public function __construct(Connection $connection, ValueCollectionFactoryInterface $valueCollectionFactory)
    {
        $this->connection = $connection;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    public function for(EntityWithFamilyVariantInterface $entity): array
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
            $identifier = $entity->getIdentifier();
            $sql = <<<SQL
SELECT identifier, raw_values
FROM pim_catalog_product
WHERE product_model_id = :parentId
AND identifier != :identifier;
SQL;
        }

        $valuesOfSiblings = [];
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'parentId' => $entity->getParent()->getId(),
                'identifier' => $identifier
            ]
        );
        foreach ($rows as $row) {
            $valuesOfSiblings[$row['identifier']] = $this->valueCollectionFactory->createFromStorageFormat(
                json_decode($row['raw_values'], true)
            );
        }

        return $valuesOfSiblings;
    }
}
