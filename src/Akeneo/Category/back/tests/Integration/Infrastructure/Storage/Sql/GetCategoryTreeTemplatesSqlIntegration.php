<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryTreeTemplates;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeTemplatesSqlIntegration extends CategoryTestCase
{
    private GetCategoryTreeTemplates $getCategoryTreeTemplates;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getCategoryTreeTemplates = $this->get(GetCategoryTreeTemplates::class);
    }

    public function testItReturnsTemplateUuidsForGivenCategoryTreeId(): void
    {
        // Arrange

        $category = $this->createOrUpdateCategory('my_category_tree');

        $deactivatedTemplateUuid = TemplateUuid::fromString('6728d119-e178-49ad-92db-bd86ed166546');
        $this->createCategoryTreeTemplate($category->getId(), $deactivatedTemplateUuid);
        $this->deactivateTemplate($deactivatedTemplateUuid->getValue());

        $templateUuid = TemplateUuid::fromString('7047253b-79ba-49e4-b6fa-4f384bb5807b');
        $this->createCategoryTreeTemplate($category->getId(), $templateUuid);

        // Act

        $result = $this->getCategoryTreeTemplates->__invoke($category->getId());

        // Assert

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(TemplateUuid::class, $result);
        $this->assertEquals(
            [
                $deactivatedTemplateUuid,
                $templateUuid,
            ],
            $result
        );
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
}
