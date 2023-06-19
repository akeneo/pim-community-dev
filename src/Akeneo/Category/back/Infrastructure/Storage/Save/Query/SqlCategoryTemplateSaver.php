<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlCategoryTemplateSaver implements CategoryTemplateSaver
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
                (UUID_TO_BIN(:uuid), :code, :labels)
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'uuid' => (string) $templateModel->getUuid(),
                'code' => (string) $templateModel->getCode(),
                'labels' => $templateModel->getLabelCollection()->normalize(),
            ],
            [
                'uuid' => \PDO::PARAM_STR,
                'code' => \PDO::PARAM_STR,
                'labels' => Types::JSON,
            ],
        );
    }

    public function update(Template $templateModel): void
    {
        $query = <<< SQL
            UPDATE pim_catalog_category_template
            SET
                labels = :labels
            WHERE uuid = UUID_TO_BIN(:uuid)
            ;
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'uuid' => (string) $templateModel->getUuid(),
                'labels' => $templateModel->getLabelCollection()->normalize(),
            ],
            [
                'uuid' => \PDO::PARAM_STR,
                'labels' => Types::JSON,
            ],
        );
    }
}
