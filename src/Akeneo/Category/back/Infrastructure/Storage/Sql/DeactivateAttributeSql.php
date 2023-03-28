<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateAttributeSql implements DeactivateAttribute
{
    public function __construct(
        private readonly Connection $connection,
        private readonly IsTemplateDeactivated $isTemplateDeactivated,
    ) {
    }

    public function execute(TemplateUuid $templateUuid, AttributeUuid $attributeUuid): void
    {
        if (($this->isTemplateDeactivated)($templateUuid)) {
            return;
        }

        $query = <<< SQL
            UPDATE pim_catalog_category_attribute 
            SET is_deactivated = true
            WHERE uuid = :attribute_uuid
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'attribute_uuid' => $attributeUuid->toBytes(),
            ],
            [
                'attribute_uuid' => \PDO::PARAM_STR,
            ],
        );
    }
}
