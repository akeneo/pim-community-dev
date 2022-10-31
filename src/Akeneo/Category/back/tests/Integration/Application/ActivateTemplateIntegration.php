<?php

namespace Akeneo\Category\back\tests\Integration\Application;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;

class ActivateTemplateIntegration extends CategoryTestCase
{
    public function testItActivateATemplateInDatabase(): void
    {
        /** @var Category $masterCategory */
        $masterCategory = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateModel = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $masterCategory->getId()->getValue()
        );

        $activateTemplateService = $this->get(ActivateTemplate::class);

        ($activateTemplateService)(
            $templateModel->getCategoryTreeId(),
            $templateModel->getCode(),
            $templateModel->getLabelCollection()
        );

        // TODO: activate SQL service instead of inMemory service in dependencies injection when GetTemplate->byUuid is implemented
        $template = $this->get(GetTemplate::class)->byUuid((string) $templateModel->getUuid());

        $this->assertEquals($templateModel->getCode(), $template->getCode());
        $this->assertEqualsCanonicalizing($templateModel->getLabelCollection(), $template->getLabelCollection());
        $this->assertEqualsCanonicalizing(
            array_keys($templateModel->getAttributeCollection()->getAttributes()),
            array_keys($template->getAttributeCollection()->getAttributes()),
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
