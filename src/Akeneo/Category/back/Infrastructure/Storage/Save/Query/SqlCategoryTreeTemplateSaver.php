<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\Domain\Model\Template;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlCategoryTreeTemplateSaver implements CategoryTreeTemplateSaver
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function insert(Template $templateModel): void
    {
        $query = <<< SQL
            INSERT INTO pim_catalog_category_tree_template
                (template_uuid, category_tree_id)
            VALUES
                (:template_identifier, :category_tree_id)
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'template_identifier' => (string) $templateModel->getUuid(),
                'category_tree_id' => $templateModel->getCategoryTreeId()->getValue(),
            ],
            [
                'template_identifier' => \PDO::PARAM_STR,
                'category_tree_id' => \PDO::PARAM_INT,
            ]
        );
    }

    public function update(Template $templateModel)
    {
        // TODO: Implement update() method.
    }

    public function linkAlreadyExists($templateModel): bool
    {
        $query = <<< SQL
            SELECT * FROM pim_catalog_category_tree_template
            WHERE template_uuid=:template_uuid 
                AND category_tree_id=:category_tree_id
            ;
        SQL;

        return $this->connection->fetchOne(
            $query,
            [
                'template_uuid' => (string) $templateModel->getUuid(),
                'category_tree_id' => $templateModel->getCategoryTreeId()->getValue(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
                'category_tree_id' => \PDO::PARAM_INT,
            ]
        );
    }
}
