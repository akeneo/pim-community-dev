<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryTreeTemplateByTemplateUuidSqlIntegration extends CategoryTestCase
{
    private DeleteCategoryTreeTemplateByTemplateUuid $deleteCategoryTreeTemplateByTemplateUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteCategoryTreeTemplateByTemplateUuid = $this->get(DeleteCategoryTreeTemplateByTemplateUuid::class);
    }

    public function testItDeletesCategoryTreeTemplate(): void
    {
        // Arrange

        $templateUuid = TemplateUuid::fromString('8605bafb-d912-4c47-b915-d31031248a7d');

        $this->useTemplateFunctionalCatalog(
            categoryCode: 'master',
            templateUuid: $templateUuid->getValue(),
        );

        // Act

        ($this->deleteCategoryTreeTemplateByTemplateUuid)($templateUuid);

        // Assert

        $this->assertCategoryTreeTemplateLinkIsDeleted($templateUuid);
        $this->assertCategoryTemplateAttributesAreDeleted($templateUuid);
        $this->assertCategoryTemplateIsDeleted($templateUuid);
    }

    private function assertCategoryTreeTemplateLinkIsDeleted(TemplateUuid $templateUuid): void
    {
        /** @var Connection */
        $connection = $this->get(Connection::class);

        $result = $connection->executeQuery(
            <<<SQL
            SELECT * FROM pim_catalog_category_tree_template
            WHERE category_template_uuid = :template_uuid
        SQL,
            [
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'template_uuid' => PDO::PARAM_STR,
            ],
        );

        $this->assertFalse(
            $result->fetchOne(),
            'The category tree template link should be deleted'
        );
    }

    private function assertCategoryTemplateAttributesAreDeleted(TemplateUuid $templateUuid): void
    {
        /** @var Connection */
        $connection = $this->get(Connection::class);

        $result = $connection->executeQuery(
            <<<SQL
            SELECT * FROM pim_catalog_category_attribute
            WHERE category_template_uuid = :template_uuid
        SQL,
            [
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'template_uuid' => PDO::PARAM_STR,
            ],
        );

        $this->assertFalse(
            $result->fetchOne(),
            'The template attributes should be deleted'
        );
    }

    private function assertCategoryTemplateIsDeleted(TemplateUuid $templateUuid): void
    {
        /** @var Connection */
        $connection = $this->get(Connection::class);

        $result = $connection->executeQuery(
            <<<SQL
            SELECT * FROM pim_catalog_category_template
            WHERE uuid = :template_uuid
        SQL,
            [
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'template_uuid' => PDO::PARAM_STR,
            ],
        );

        $this->assertFalse(
            $result->fetchOne(),
            'The template should be deleted'
        );
    }
}
