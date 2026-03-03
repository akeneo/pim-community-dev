<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetCategoryTreeByCategoryTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;

class SqlCategoryTreeTemplateSaverIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryTemplateInDatabase(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $retrievedCategoryTree = ($this->get(GetCategoryTreeByCategoryTemplate::class))($templateModel->getUuid());

        $this->assertEquals($category->getId(), $retrievedCategoryTree->getId());
    }

    public function testItDoesNotInsertNewCategoryTemplateOnDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);

        $this->deactivateTemplate($templateModel->getUuid()->getValue());

        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $retrievedCategoryTree = ($this->get(GetCategoryTreeByCategoryTemplate::class))($templateModel->getUuid());

        $this->assertNull($retrievedCategoryTree);
    }
}
