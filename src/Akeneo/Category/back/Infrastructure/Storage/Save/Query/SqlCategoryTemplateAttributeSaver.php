<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

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
    private array $defaultParams;

    private array $defaultTypes;

    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function insert(TemplateUuid $templateUuid, AttributeCollection $attributeCollection): void
    {
        $this->defaultParams['template_uuid'] = (string) $templateUuid;
        $this->defaultTypes['template_uuid'] = \PDO::PARAM_STR;

        foreach ($attributeCollection->getAttributes() as $attribute) {
            $this->insertAttribute($attribute);
        }
    }

    public function update(TemplateUuid $templateUuid, AttributeCollection $attributeCollection): void
    {
        // TODO: Implement update() method.
    }

    private function insertAttribute(Attribute $attribute): void
    {
        $query = <<<SQL
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
            VALUES
                (
                    UUID_TO_BIN(:uuid),
                    :code,
                    UUID_TO_BIN(:template_uuid),
                    :labels,
                    :attribute_type,
                    :attribute_order,
                    :is_required,
                    :is_scopable,
                    :is_localizable,
                    :additional_properties
                 )
            ;
SQL;

        $params = array_merge(
            $this->defaultParams,
            [
                'uuid' => (string) $attribute->getUuid(),
                'code' => (string) $attribute->getCode(),
                'labels' => $attribute->getLabelCollection()->normalize(),
                'attribute_type' => (string) $attribute->getType(),
                'attribute_order' => $attribute->getOrder()->intValue(),
                'is_required' => false, // TODO
                'is_scopable' => false, // TODO
                'is_localizable' => false, // TODO
                'additional_properties' => [], // TODO
            ]
        );

        $types = array_merge(
            $this->defaultTypes,
            [
                'uuid'=> \PDO::PARAM_STR,
                'code'=> \PDO::PARAM_STR,
                'labels'=> Types::JSON,
                'attribute_type'=> \PDO::PARAM_STR,
                'attribute_order'=> \PDO::PARAM_INT,
                'is_required'=> \PDO::PARAM_BOOL,
                'is_scopable'=> \PDO::PARAM_BOOL,
                'is_localizable'=> \PDO::PARAM_BOOL,
                'additional_properties'=> Types::JSON,
            ]
        );

        $this->connection->executeQuery(
            $query,
            $params,
            $types
        );
    }
}
