<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateAttributeSql implements GetAttribute
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     * @throws \JsonException
     * @throws \Doctrine\DBAL\Exception
     */
    public function byTemplateUuid(TemplateUuid $uuid): AttributeCollection
    {
        $query = <<< SQL
            SELECT 
                BIN_TO_UUID(uuid) as uuid,
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
            WHERE category_template_uuid = :template_uuid
            AND is_deactivated = 0
            ORDER BY attribute_order;
        SQL;

        $results = $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $uuid->toBytes(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
            ],
        )->fetchAllAssociative();

        return AttributeCollection::fromArray(array_map(static function ($results) {
            return Attribute::fromDatabase($results);
        }, $results));
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
            AND is_deactivated = 0;
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

    public function byUuid(AttributeUuid $attributeUuid): ?Attribute
    {
        $attributes = $this->byUuids([$attributeUuid]);
        if ($attributes->count() > 0) {
            return $attributes->getAttributes()[0];
        } else {
            return null;
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     * @throws Exception
     */
    public function byCode(AttributeCode $attributeCode): Attribute
    {
        $query = <<< SQL
            SELECT 
                BIN_TO_UUID(uuid) as uuid,
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
            WHERE code = :code
            AND is_deactivated = 0;
        SQL;

        $attribute = $this->connection->executeQuery(
            $query,
            ['code' => (string) $attributeCode],
            ['code' => \PDO::PARAM_STR],
        )->fetchAssociative();

        return Attribute::fromDatabase($attribute);
    }
}
