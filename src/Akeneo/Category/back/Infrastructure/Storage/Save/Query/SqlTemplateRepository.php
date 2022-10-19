<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Query\UpsertTemplate;
use Akeneo\Category\Domain\Model\Template;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Save values from model into TODO_GET_NAME table:
 * The values are inserted if the id is new, they are updated if the id already exists.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InsertTemplateSql implements Templatereposito
{
    public function __construct(
        private Connection $connection,
        private GetTemplate $getTemplate,
    ) {
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function execute(Template $templateModel): void
    {
        if (null !== $this->getTemplate->byUuid((string) $templateModel->getUuid())) {
            $this->updateCategory($templateModel);
        } else {
            $this->insertCategory($templateModel);
        }
    }




    /**
     * @throws Exception
     */
    private function insertTemplate(Template $templateModel): void
    {
        /*
         private TemplateUuid $uuid,
        private TemplateCode $code,
        private LabelCollection $labelCollection,
        private ?CategoryId $categoryTreeId,
        private AttributeCollection $attributeCollection,
         * */


        $query = <<< SQL
            INSERT INTO pim_catalog_category_template
                (identifier, code, labels)
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
    }
}
