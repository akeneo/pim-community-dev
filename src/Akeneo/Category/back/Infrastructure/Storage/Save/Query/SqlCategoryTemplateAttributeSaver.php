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

    public function update(Attribute $attribute): void
    {
        if (($this->isTemplateDeactivated)($attribute->getTemplateUuid())) {
            return;
        }

        $query = <<<SQL
            UPDATE pim_catalog_category_attribute
            SET attribute_type = :type, labels = :labels
            WHERE uuid = UUID_TO_BIN(:uuid);
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'type' => (string) $attribute->getType(),
                'labels' => $attribute->getLabelCollection()->normalize(),
                'uuid' => $attribute->getUuid()->getValue(),
            ],
            [
                'type' => \PDO::PARAM_STR,
                'labels' => Types::JSON,
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
