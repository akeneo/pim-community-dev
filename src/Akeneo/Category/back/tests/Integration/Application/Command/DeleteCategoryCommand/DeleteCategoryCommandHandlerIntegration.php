<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Application\Command\DeleteCategoryCommand;

use Akeneo\Category\Application\Command\DeleteCategoryCommand\DeleteCategoryCommand;
use Akeneo\Category\Application\Command\DeleteCategoryCommand\DeleteCategoryCommandHandler;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryCommandHandlerIntegration extends CategoryTestCase
{
    private DeleteCategoryCommandHandler $deleteCategoryCommandHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteCategoryCommandHandler = $this->get(DeleteCategoryCommandHandler::class);
    }

    public function testItDeletesACategory(): void
    {
        // Arrange

        $categoryTree = $this->createOrUpdateCategory('my_category_tree');

        // Act

        $this->deleteCategoryCommandHandler->__invoke(
            new DeleteCategoryCommand($categoryTree->getId()->getValue())
        );

        // Assert

        $this->assertNull($this->getCategory($categoryTree->getId()));
    }

    public function testItDoesNotDeleteACategoryIfItDoesNotExist(): void
    {
        // Act

        $this->deleteCategoryCommandHandler->__invoke(
            new DeleteCategoryCommand(42)
        );

        // Assert

        $this->expectNotToPerformAssertions();
    }

    public function testItDeletesCategoryTemplatesWhenARootCategoryIsDeleted(): void
    {
        // Arrange

        $categoryTree = $this->createOrUpdateCategory('my_category_tree');

        $deactivatedTemplateUuid = TemplateUuid::fromString('6728d119-e178-49ad-92db-bd86ed166546');
        $this->createCategoryTreeTemplate($categoryTree->getId(), $deactivatedTemplateUuid);
        $this->deactivateTemplate($deactivatedTemplateUuid->getValue());

        $templateUuid = TemplateUuid::fromString('7047253b-79ba-49e4-b6fa-4f384bb5807b');
        $this->createCategoryTreeTemplate($categoryTree->getId(), $templateUuid);

        // Act

        $this->deleteCategoryCommandHandler->__invoke(
            new DeleteCategoryCommand($categoryTree->getId()->getValue())
        );

        // Assert

        $this->assertNull($this->getCategory($categoryTree->getId()));
        $this->assertFalse($this->categoryTemplateExists($deactivatedTemplateUuid));
        $this->assertFalse($this->categoryTemplateExists($templateUuid));
    }

    public function testItDoesNotDeleteCategoryTemplatesWhenANonRootCategoryIsDeleted(): void
    {
        // Arrange

        $categoryTree = $this->createOrUpdateCategory('my_category_tree');
        $childCategory = $this->createOrUpdateCategory(
            'my_child_category',
            rootId: $categoryTree->getId()->getValue(),
            parentId: $categoryTree->getId()->getValue(),
        );

        $templateUuid = TemplateUuid::fromString('7047253b-79ba-49e4-b6fa-4f384bb5807b');
        $this->createCategoryTreeTemplate($categoryTree->getId(), $templateUuid);

        // Act

        $this->deleteCategoryCommandHandler->__invoke(
            new DeleteCategoryCommand($childCategory->getId()->getValue())
        );

        // Assert

        $this->assertNull($this->getCategory($childCategory->getId()));
        $this->assertTrue($this->categoryTemplateExists($templateUuid));
    }

    private function createCategoryTreeTemplate(CategoryId $categoryId, TemplateUuid $templateUuid): void
    {
        $template = Template::fromDatabase([
            'uuid' => $templateUuid->getValue(),
            'code' => 'my_template',
            'labels' => null,
            'category_id' => (string)$categoryId->getValue(),
        ]);

        /** @var CategoryTemplateSaver */
        $categoryTemplateSaver = $this->get(CategoryTemplateSaver::class);
        $categoryTemplateSaver->insert($template);

        /** @var CategoryTreeTemplateSaver */
        $categoryTreeTemplateSaver = $this->get(CategoryTreeTemplateSaver::class);
        $categoryTreeTemplateSaver->insert($template);
    }

    private function getCategory(CategoryId $categoryId): ?Category
    {
        /** @var GetCategoryInterface */
        $getCategory = $this->get(GetCategoryInterface::class);

        return $getCategory->byId($categoryId->getValue());
    }

    private function categoryTemplateExists(TemplateUuid $templateUuid): bool
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

        return $result->fetchOne() !== false;
    }
}
