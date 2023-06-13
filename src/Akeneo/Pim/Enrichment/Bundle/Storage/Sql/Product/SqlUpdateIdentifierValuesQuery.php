<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\UpdateIdentifierValuesQuery;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValueInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlUpdateIdentifierValuesQuery implements UpdateIdentifierValuesQuery
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function forProducts(array $products): void
    {
        $parameters = \implode(', ', \array_fill(0, \count($products), '(?, ?)'));
        $statement = $this->connection->prepare(
            \sprintf(
                <<<SQL
                INSERT INTO pim_catalog_product_identifiers(product_uuid, identifiers)
                VALUES %s
                ON DUPLICATE KEY UPDATE identifiers = VALUES(identifiers);
                SQL,
                $parameters
            )
        );

        $paramIndex = 0;
        foreach ($products as $product) {
            $statement->bindValue(++$paramIndex, $product->getUuid()->getBytes(), ParameterType::BINARY);
            $identifierValues = \array_map(
                static fn (IdentifierValueInterface $value): string => \sprintf(
                    '%s#%s',
                    $value->getAttributeCode(),
                    $value->getData()
                ),
                $product->getValues()->filter(
                    static fn (ValueInterface $value): bool => $value instanceof IdentifierValueInterface
                )->getValues()
            );
            $statement->bindValue(++$paramIndex, \json_encode($identifierValues));
        }

        $statement->executeStatement();
    }
}
