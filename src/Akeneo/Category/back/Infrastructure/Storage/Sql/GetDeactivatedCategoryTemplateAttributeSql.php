<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetDeactivatedAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDeactivatedCategoryTemplateAttributeSql implements GetDeactivatedAttribute
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param AttributeUuid[] $attributeUuids
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     * @throws \JsonException
     */
    public function byUuids(array $attributeUuids): AttributeCollection
    {
        $placeholders = \implode(
            ',',
            \array_fill(0, \count($attributeUuids), 'UUID_TO_BIN(?)'),
        );

        $sql = <<< SQL
            SELECT BIN_TO_UUID(uuid) as uuid,
                code, 
                BIN_TO_UUID(category_template_uuid) as category_template_uuid,
                labels, 
                attribute_type, 
                attribute_order, 
                is_required, 
                is_scopable, 
                is_localizable, 
                additional_properties
            FROM pim_catalog_category_attribute
            WHERE uuid IN ({$placeholders})
            AND is_deactivated = 1;
        SQL;

        $statement = $this->connection->prepare($sql);
        $placeholderIndex = 0;
        foreach ($attributeUuids as $uuid) {
            $statement->bindValue(++$placeholderIndex, (string) $uuid, \PDO::PARAM_STR);
        }

        $categoryAttributes = $statement
            ->executeQuery()
            ->fetchAllAssociative();

        $attributes = array_map(static function ($attributes) {
            return Attribute::fromDatabase($attributes);
        }, $categoryAttributes);

        return AttributeCollection::fromArray($attributes);
    }
}
