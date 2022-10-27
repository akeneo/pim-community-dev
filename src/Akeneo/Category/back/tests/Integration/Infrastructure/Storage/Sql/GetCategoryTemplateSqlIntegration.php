<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateSqlIntegration extends CategoryTestCase
{
    public function testItRetrieveEnTemplateFromUuid(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);

        $retrievedTemplate = $this->get(GetTemplate::class)->byUuid((string) $templateModel->getUuid());

        $this->assertEquals($templateModel->getCode(), $retrievedTemplate->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
