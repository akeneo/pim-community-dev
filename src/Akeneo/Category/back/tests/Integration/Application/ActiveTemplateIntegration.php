<?php

namespace Akeneo\Category\back\tests\Integration\Application;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\back\tests\Integration\CategoryTemplateTrait;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\TestCase;

class ActiveTemplateIntegration extends TestCase
{
    use CategoryTemplateTrait;

    public function testItActivateATemplateInDatabase()
    {
        /** @var Category $masterCategory */
        $masterCategory = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateModel = $this->generateStaticCategoryTemplate(categoryTreeId: $masterCategory->getId()->getValue());

        $activateTemplateService = $this->get(ActivateTemplate::class);

        ($activateTemplateService)(
            $templateModel->getCategoryTreeId(),
            $templateModel->getCode(),
            $templateModel->getLabelCollection()
        );

        // TODO: activate SQL service instead of inMemory service in dependencies injection when GetTemplate->byUuid is implemented
        $template = $this->get(GetTemplate::class)->byUuid((string) $templateModel->getUuid());

        $this->assertEquals($templateModel->getCode(), $template->getCode());
        // TODO change for existing categoryId when inMemory service replaced with sql service
        //$this->assertEquals($templateModel->getCategoryTreeId(), $template->getCategoryTreeId());
        $this->assertEqualsCanonicalizing($templateModel->getLabelCollection(), $template->getLabelCollection());
        $this->assertEqualsCanonicalizing(
            array_keys($templateModel->getAttributeCollection()->getAttributes()),
            array_keys($template->getAttributeCollection()->getAttributes()),
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
