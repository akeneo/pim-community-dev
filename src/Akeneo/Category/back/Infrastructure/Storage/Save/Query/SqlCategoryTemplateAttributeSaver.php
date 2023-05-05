<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlCategoryTemplateAttributeSaver implements CategoryTemplateAttributeSaver
{
    public function __construct(
        private readonly Connection $connection,
        private readonly IsTemplateDeactivated $isTemplateDeactivated,
    ) {
    }

    public function insert(TemplateUuid $templateUuid, AttributeCollection $attributeCollection): void
    {
        if (($this->isTemplateDeactivated)($templateUuid)) {
            return;
        }

        $this->insertAttributes($attributeCollection->getAttributes());
    }

    public function update(TemplateUuid $templateUuid, Attribute $attribute): void
    {
        if (($this->isTemplateDeactivated)($templateUuid)) {
            return;
        }

        $query = <<<SQL
            UPDATE pim_catalog_category_attribute
            SET code = :code, 
                category_template_uuid = UUID_TO_BIN(:template_uuid), 
                labels = :labels,
                attribute_type = :type,
                attribute_order = :order,
                is_required = :is_required,
                is_scopable = :is_scopable,
                is_localizable = :is_localizable,
                additional_properties = :properties
            WHERE uuid = UUID_TO_BIN(:uuid);
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'code' => (string) $attribute->getCode(),
                'template_uuid' => $attribute->getTemplateUuid()->getValue(),
                'labels' => $attribute->getLabelCollection()->normalize(),
                'type' => (string)  $attribute->getType(),
                'order' => (int)  $attribute->getOrder(),
                'is_required' => (bool)  $attribute->isRequired(),
                'is_scopable' => (bool)  $attribute->isScopable(),
                'is_localizable' => (bool)  $attribute->isLocalizable(),
                'properties' => $attribute->getAdditionalProperties()->normalize(),
                'uuid' => $attribute->getUuid()->getValue(),
            ],
            [
                'code' => \PDO::PARAM_STR,
                'template_uuid' => \PDO::PARAM_STR,
                'labels' => Types::JSON,
                'type' => \PDO::PARAM_STR,
                'order' => \PDO::PARAM_INT,
                'is_required' => \PDO::PARAM_BOOL,
                'is_scopable' => \PDO::PARAM_BOOL,
                'is_localizable' => \PDO::PARAM_BOOL,
                'properties' => Types::JSON,
                'uuid' => \PDO::PARAM_STR,
            ],
        );
    }

    /**
     * @param array<Attribute> $attributes
     *
     * @return void
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function insertAttributes(array $attributes)
    {
        $placeholders = \implode(
            ',',
            \array_fill(0, \count($attributes), '(UUID_TO_BIN(?), ?, UUID_TO_BIN(?), ?, ?, ?, ?, ?, ?, ?)'),
        );
        $statement = $this->connection->prepare(
            <<<SQL
            INSERT INTO pim_catalog_category_attribute
            (
                uuid,
                code,
                category_template_uuid,
                labels,
                attribute_type,
                attribute_order,
                is_required,
                is_scopable,
                is_localizable,
                additional_properties
            )
            VALUES {$placeholders} ;
            SQL
        );

        $placeholderIndex = 0;
        foreach ($attributes as $attribute) {
            $statement->bindValue(++$placeholderIndex, (string) $attribute->getUuid(), \PDO::PARAM_STR);
            $statement->bindValue(++$placeholderIndex, (string) $attribute->getCode(), \PDO::PARAM_STR);
            $statement->bindValue(++$placeholderIndex, (string) $attribute->getTemplateUuid(), \PDO::PARAM_STR);
            $statement->bindValue(++$placeholderIndex, $attribute->getLabelCollection()->normalize(), Types::JSON);
            $statement->bindValue(++$placeholderIndex, (string) $attribute->getType(), \PDO::PARAM_STR);
            $statement->bindValue(++$placeholderIndex, $attribute->getOrder()->intValue(), \PDO::PARAM_INT);
            $statement->bindValue(++$placeholderIndex, $attribute->isRequired()->getValue(), \PDO::PARAM_BOOL);
            $statement->bindValue(++$placeholderIndex, $attribute->isScopable()->getValue(), \PDO::PARAM_BOOL);
            $statement->bindValue(++$placeholderIndex, $attribute->isLocalizable()->getValue(), \PDO::PARAM_BOOL);
            $statement->bindValue(++$placeholderIndex, $attribute->getAdditionalProperties()->normalize(), Types::JSON);
        }

        $statement->executeQuery();
    }
}
