<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
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
            SELECT * FROM pim_catalog_category_template category_template
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
                $result['uuid'],
                $result['code'],
                $result['labels'],
                $result['category_tree_id'],
                // TODO this must be implemented at the same time we implement all getTemplate queries
                AttributeCollection::fromArray([]),
            );
        }

        return $template;
    }
}
