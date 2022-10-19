<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Template\TemplateRepository;
use Akeneo\Category\Domain\Model\Template;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlTemplateRepository implements TemplateRepository
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
            INSERT INTO pim_catalog_category_template
                (uuid, code, labels)
            VALUES
                (:identifier, :code, :labels)
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'identifier' => (string) $templateModel->getUuid(),
                'code' => (string) $templateModel->getCode(),
                'labels' => json_encode($templateModel->getLabelCollection()->normalize()),
            ],
            [
                'identifier' => \PDO::PARAM_STR,
                'code' => \PDO::PARAM_STR,
                'labels' => \PDO::PARAM_STR,
            ]
        );

        // We must update the category table to add the foreign key pointing to the inserted template.
        // We update the pim_catalog_category.template_uuid only if the category has no linked template.
        $query = <<< SQL
            UPDATE pim_catalog_category
            SET category_template_uuid=:template_uuid
            WHERE category_template_uuid IS NULL 
              AND (id=:category_id OR parent_id=:category_id)
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => (string) $templateModel->getUuid(),
                'category_id' => $templateModel->getCategoryTreeId(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
                'category_id' => \PDO::PARAM_INT,
            ]
        );
    }

    public function update(Template $templateModel)
    {
        // TODO: Implement update() method.
    }
}
