<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\CheckTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckTemplateSqlIntegration extends CategoryTestCase
{
    public function testItChecksTheTemplateExists(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplate($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);

        $retrievedTemplate = $this->get(CheckTemplate::class)->codeExists($templateModel->getCode());

        $this->assertTrue($retrievedTemplate);
    }

    public function testItIgnoresDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplate($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->deactivateTemplate($templateUuid);

        $retrievedTemplate = $this->get(CheckTemplate::class)->codeExists($templateModel->getCode());

        $this->assertFalse($retrievedTemplate);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
