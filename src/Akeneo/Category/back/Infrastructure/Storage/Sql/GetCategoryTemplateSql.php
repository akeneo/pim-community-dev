<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Enrichment\Template;
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
}
