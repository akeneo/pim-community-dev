<?php

namespace Akeneo\Category\back\tests\Integration\Application;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
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
        $labelCollectionExpected = LabelCollection::fromArray(["en_US" => "Master catalog template"]);
        $this->assertEqualsCanonicalizing($labelCollectionExpected, $template->getLabelCollection());
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
