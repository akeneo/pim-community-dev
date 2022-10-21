<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateSql implements GetTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    public function byUuid(string $templateUuid): ?Template
    {
        // TODO implement byUuid method + then plug this class instead of GetTemplateInMemory
        return null;
    }

    /**
     * @param TemplateCode $templateCode
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function exists(TemplateCode $templateCode): bool
    {
        $query = <<<SQL
            SELECT * FROM pim_catalog_template
            WHERE code=:template_code
        SQL;

        return $this->connection->executeQuery(
            $query,
            ['template_code' => (string) $templateCode],
            ['template_code' => \PDO::PARAM_STR],
        )->fetchOne();
    }
}
