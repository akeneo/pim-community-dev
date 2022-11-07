<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoryTemplateIntegration extends CategoryTestCase
{
    public function testGetTemplateById(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedTemplate = $this->givenTemplate($templateUuid, $category->getId());
        $this->get(CategoryTemplateSaver::class)->insert($expectedTemplate);
        $this->get(CategoryTreeTemplateSaver::class)->insert($expectedTemplate);
        $expectedTemplate->setAttributeCollection(null);

        $savedTemplate = $this->get(GetTemplate::class)->byUuid(TemplateUuid::fromString($templateUuid));
        $this->assertEquals($expectedTemplate, $savedTemplate);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
