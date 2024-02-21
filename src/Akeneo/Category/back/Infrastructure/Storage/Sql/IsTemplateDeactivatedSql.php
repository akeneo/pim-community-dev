<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsTemplateDeactivatedSql implements IsTemplateDeactivated
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(TemplateUuid $templateUuid): bool
    {
        $query = <<<SQL
            SELECT is_deactivated
            FROM pim_catalog_category_template
            WHERE uuid = :template_uuid;
        SQL;

        $result = $this->connection->executeQuery($query, [
            'template_uuid' => $templateUuid->toBytes(),
        ])->fetchAssociative();

        return $result['is_deactivated'] === '1';
    }
}
