<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateByCategoryTreeSql implements GetCategoryTemplateByCategoryTree
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param CategoryId $categoryTreeId
     * @return ?Template
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(CategoryId $categoryTreeId): ?Template
    {
        $query = <<<SQL
            SELECT 
                BIN_TO_UUID(category_template.uuid) as uuid,
                category_template.code as code,
                category_template.labels as labels,
                category_tree_template.category_tree_id as category_tree_id
            FROM pim_catalog_category_template category_template
            JOIN pim_catalog_category_tree_template category_tree_template 
                ON category_tree_template.category_template_uuid=category_template.uuid
            WHERE category_tree_id=:category_id
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            ['category_id' => $categoryTreeId->getValue()],
            ['category_id' => \PDO::PARAM_INT],
        )->fetchAssociative();

        $template =null;
        if ($result) {
            $template = new Template(
                TemplateUuid::fromString($result['uuid']),
                new TemplateCode($result['code']),
                $result['labels'] ?
                    LabelCollection::fromArray(
                        json_decode(
                            $result['labels'],
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        )
                    )
                    : LabelCollection::fromArray([]),
                new CategoryId((int) $result['category_tree_id']),
                // TODO this must be implemented at the same time we implement all getTemplate queries
                AttributeCollection::fromArray([]),
            );
        }

        return $template;
    }
}
