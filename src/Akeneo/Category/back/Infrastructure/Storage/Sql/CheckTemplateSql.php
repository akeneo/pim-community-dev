<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\CheckTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckTemplateSql implements CheckTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    public function codeExists(TemplateCode $templateCode): bool
    {
        $query = <<<SQL
            SELECT count(1) FROM pim_catalog_category_template
            WHERE code=:template_code
            AND (is_deactivated IS NULL OR is_deactivated = 0)
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            ['template_code' => (string) $templateCode],
            ['template_code' => \PDO::PARAM_STR],
        )->fetchOne();

        return (bool) $result;
    }
}
