<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCategoryTemplateAttributesOrderSql implements UpdateCategoryTemplateAttributesOrder
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function fromAttributeCollection(AttributeCollection $attributeList): void
    {
        $queries = \implode(
            ';',
            \array_fill(
                0,
                $attributeList->count(),
                'UPDATE pim_catalog_category_attribute as pcca
                SET pcca.attribute_order = ?
                WHERE uuid = ?',
            ),
        );

        $statement = $this->connection->prepare(<<<SQL
            $queries
        SQL);

        $queryIndex = 0;
        foreach ($attributeList->getAttributes() as $attribute) {
            $statement->bindValue(++$queryIndex, $attribute->getOrder()->intValue(), \PDO::PARAM_INT);
            $statement->bindValue(++$queryIndex, $attribute->getUuid()->toBytes(), \PDO::PARAM_STR);
        }

        $statement->executeQuery();
    }
}
