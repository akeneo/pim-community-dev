<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\Query\DeleteTemplateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryTemplateAttributeSql implements DeleteTemplateAttribute
{
    public function __construct(
        private readonly Connection $connection,
        private readonly IsTemplateDeactivated $isTemplateDeactivated,
    ) {
    }

    public function delete(TemplateUuid $templateUuid, AttributeUuid $attributeUuid): void
    {
        if (($this->isTemplateDeactivated)($templateUuid)) {
            return;
        }

        $query = <<< SQL
            DELETE FROM pim_catalog_category_attribute
            WHERE category_template_uuid = UUID_TO_BIN(:template_uuid) 
            AND uuid = UUID_TO_BIN(:attribute_uuid);
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => $templateUuid->getValue(),
                'attribute_uuid' => $attributeUuid->getValue(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
                'attribute_uuid' => \PDO::PARAM_STR,
            ],
        );
    }
}
