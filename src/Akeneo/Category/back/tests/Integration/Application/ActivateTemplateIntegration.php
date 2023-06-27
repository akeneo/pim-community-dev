<?php

namespace Akeneo\Category\back\tests\Integration\Application;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Query\GetTemplate;
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

        $templateUuid = ($activateTemplateService)(
            $templateModel->getCategoryTreeId(),
            $templateModel->getCode(),
            $templateModel->getLabelCollection()
        );

        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        $attributes = $this->get(GetAttribute::class)->byTemplateUuid($templateUuid);

        $attributesExpected = self::givenAttributes($templateUuid);

        $this->assertEquals('master_template', $template->getCode());
        $this->assertEqualsCanonicalizing(
            array_keys($attributesExpected->getAttributes()),
            array_keys($attributes->getAttributes()),
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
