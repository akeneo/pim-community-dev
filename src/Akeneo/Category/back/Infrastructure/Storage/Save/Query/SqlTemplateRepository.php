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
    }

    public function update(Template $templateModel)
    {
        // TODO: Implement update() method.
    }
}
