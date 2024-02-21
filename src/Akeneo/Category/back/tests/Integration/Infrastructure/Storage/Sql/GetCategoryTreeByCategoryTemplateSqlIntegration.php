<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTreeByCategoryTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeByCategoryTemplateSqlIntegration extends CategoryTestCase
{
    public function testItRetrieveACategoryTreeByCategoryTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $retrievedCategoryTree = ($this->get(GetCategoryTreeByCategoryTemplate::class))($templateModel->getUuid());

        $this->assertEquals($category->getId(), $retrievedCategoryTree->getId());
        $this->assertEquals($category->getCode(), $retrievedCategoryTree->getCode());
        $this->assertEquals($templateModel->getLabelCollection(), $retrievedCategoryTree->getCategoryTreeTemplate()->getTemplateLabels());
    }

    public function testItIgnoresDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->deactivateTemplate($templateModel->getUuid()->getValue());

        $retrievedCategoryTree = ($this->get(GetCategoryTreeByCategoryTemplate::class))($templateModel->getUuid());

        $this->assertNull($retrievedCategoryTree->getCategoryTreeTemplate());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
