<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
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
class GetCategoryTemplateByCategoryTreeSqlIntegration extends CategoryTestCase
{
    public function testItRetrieveACategoryTemplateByCategoryTree(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $retrievedTemplate = ($this->get(GetCategoryTemplateByCategoryTree::class))($category->getId());

        $this->assertEquals($templateModel->getUuid(), $retrievedTemplate->getUuid());
        $this->assertEquals($templateModel->getCode(), $retrievedTemplate->getCode());
    }

    public function testItIgnoresDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->deactivateTemplate($templateModel->getUuid()->getValue());

        $retrievedTemplate = ($this->get(GetCategoryTemplateByCategoryTree::class))($category->getId());

        $this->assertNull($retrievedTemplate);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
