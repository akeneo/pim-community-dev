<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Exception\TemplateNotFoundException;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoryTemplateSqlIntegration extends CategoryTestCase
{
    public function testGetTemplateByUuid(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplate($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $retrievedTemplate = $this->get(GetTemplate::class)->byUuid(TemplateUuid::fromString($templateUuid));

        $this->assertEquals($templateModel->getUuid(), $retrievedTemplate->getUuid());
    }

    public function testItIgnoresDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplate($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->deactivateTemplate($templateUuid);

        $this->expectException(TemplateNotFoundException::class);

        $this->get(GetTemplate::class)->byUuid(TemplateUuid::fromString($templateUuid));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
