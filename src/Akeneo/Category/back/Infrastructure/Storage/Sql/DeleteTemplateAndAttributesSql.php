<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\DeleteTemplateAndAttributes;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTemplateAndAttributesSql implements DeleteTemplateAndAttributes
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(TemplateUuid $templateUuid): void
    {
        // pim_catalog_category_attributes table is cleared subsequently thanks to on delete cascade
        $query = <<< SQL
            DELETE FROM pim_catalog_category_template
            WHERE uuid = :template_uuid
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
            ],
        );
    }
}
