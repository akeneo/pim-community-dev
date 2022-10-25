<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlCategoryTemplateAttributeSaver implements CategoryTemplateAttributeSaver
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function insert(TemplateUuid $templateUuid, AttributeCollection $attributeCollection): void
    {
        $queries = '';
        $params = ['template_uuid' => (string) $templateUuid];
        $types = ['template_uuid' => \PDO::PARAM_STR];
        $loopIndex = 0;
        foreach ($attributeCollection->getAttributes() as $attribute) {
            /** @var Attribute $attribute */
            $queries .= $this->buildInsertQuery($loopIndex);

            $params['uuid' . $loopIndex] = (string) $attribute->getUuid();
            $params['code' . $loopIndex] = (string) $attribute->getCode();
            $params['labels' . $loopIndex] = json_encode($attribute->getLabelCollection()->normalize());
            $params['attribute_type' . $loopIndex] = (string) $attribute->getType();
            $params['attribute_order' . $loopIndex] = $attribute->getOrder()->intValue();
            $params['is_required' . $loopIndex] = false; // TODO
            $params['is_scopable' . $loopIndex] = false; // TODO
            $params['is_localizable' . $loopIndex] = false; // TODO
            $params['additional_properties' . $loopIndex] = json_encode([]); // TODO

            $types['uuid' . $loopIndex] = \PDO::PARAM_STR;
            $types['code' . $loopIndex] = \PDO::PARAM_STR;
            $types['labels' . $loopIndex] = \PDO::PARAM_STR;
            $types['attribute_type' . $loopIndex] = \PDO::PARAM_STR;
            $types['attribute_order' . $loopIndex] = \PDO::PARAM_INT;
            $types['is_required' . $loopIndex] = \PDO::PARAM_BOOL;
            $types['is_scopable' . $loopIndex] = \PDO::PARAM_BOOL;
            $types['is_localizable' . $loopIndex] = \PDO::PARAM_BOOL;
            $types['additional_properties' . $loopIndex] = \PDO::PARAM_STR;

            ++$loopIndex;
        }

        if (empty($queries)) {
            // template has no attribute
            return;
        }

        $this->connection->executeQuery(
            $queries,
            $params,
            $types,
        );
    }

    public function update(TemplateUuid $templateUuid, AttributeCollection $attributeCollection): void
    {
        // TODO: Implement update() method.
    }

    private function buildInsertQuery(int $loopIndex): string
    {
        return <<<SQL
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
                    UUID_TO_BIN(:uuid$loopIndex),
                    :code$loopIndex,
                    UUID_TO_BIN(:template_uuid),
                    :labels$loopIndex,
                    :attribute_type$loopIndex,
                    :attribute_order$loopIndex,
                    :is_required$loopIndex,
                    :is_scopable$loopIndex,
                    :is_localizable$loopIndex,
                    :additional_properties$loopIndex
                 )
            ;
SQL;
    }
}
