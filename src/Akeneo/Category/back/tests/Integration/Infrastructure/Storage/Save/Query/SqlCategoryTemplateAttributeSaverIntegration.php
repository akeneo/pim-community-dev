<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;

class SqlCategoryTemplateAttributeSaverIntegration extends CategoryTestCase
{
    private GetCategoryInterface $getCategory;
    private CategoryTemplateSaver $categoryTemplateSaver;
    private CategoryTreeTemplateSaver $categoryTreeTemplateSaver;
    private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver;
    private GetTemplate $getTemplate;
    private GetAttribute $getAttribute;
    protected function setUp(): void
    {
        parent::setUp();
        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->categoryTemplateSaver = $this->get(CategoryTemplateSaver::class);
        $this->categoryTreeTemplateSaver = $this->get(CategoryTreeTemplateSaver::class);
        $this->categoryTemplateAttributeSaver = $this->get(CategoryTemplateAttributeSaver::class);
        $this->getTemplate = $this->get(GetTemplate::class);
        $this->getAttribute = $this->get(GetAttribute::class);
    }


    public function testInsertsNewCategoryAttributeInDatabase(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);
        $this->categoryTemplateAttributeSaver->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        /** @var Template $insertedTemplate */
        $insertedTemplate = $this->getTemplate->byUuid($templateModel->getUuid());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($templateModel->getUuid());
        $insertedTemplate->setAttributeCollection($insertedAttributes);

        $this->assertEqualsCanonicalizing(
            array_keys($templateModel->getAttributeCollection()->getAttributes()),
            array_keys($insertedTemplate->getAttributeCollection()->getAttributes())
        );
    }

    public function testItDoesNotInsertNewCategoryAttributeOnDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);

        $this->deactivateTemplate($templateUuid);

        $this->categoryTemplateAttributeSaver->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        $insertedAttributes = $this->getAttribute->byTemplateUuid($templateModel->getUuid());

        $this->assertEquals(AttributeCollection::fromArray([]), $insertedAttributes);
    }

    public function testItChangesAttributeToTextArea(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);

        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        $insertedAttributes = $this->getAttribute->byTemplateUuid($templateModel->getUuid());

        $longDescription = $insertedAttributes->getAttributeByCode('long_description');
        $this->assertEquals((string) $longDescription->getType(), AttributeType::RICH_TEXT);

        $longDescription->update(isRichRextArea: false, labels: null);
        $this->categoryTemplateAttributeSaver->update($longDescription);

        $longDescription = $insertedAttributes->getAttributeByCode('long_description');
        $this->assertEquals((string) $longDescription->getType(), AttributeType::TEXTAREA);
    }

    public function testItChangesAttributeToRichText(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);

        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        $insertedAttributes = $this->getAttribute->byTemplateUuid($templateModel->getUuid());

        $seoMetaDescription = $insertedAttributes->getAttributeByCode('seo_meta_description');
        $this->assertEquals((string) $seoMetaDescription->getType(), AttributeType::TEXTAREA);

        $seoMetaDescription->update(isRichRextArea: true, labels: null);
        $this->categoryTemplateAttributeSaver->update($seoMetaDescription);

        $seoMetaDescription = $insertedAttributes->getAttributeByCode('seo_meta_description');
        $this->assertEquals((string) $seoMetaDescription->getType(), AttributeType::RICH_TEXT);
    }

    public function testItAddsLabelsToAttribute(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);

        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        $insertedAttributes = $this->getAttribute->byTemplateUuid($templateModel->getUuid());

        $longDescription = $insertedAttributes->getAttributeByCode('long_description');
        $this->assertEquals((string) $longDescription->getType(), AttributeType::RICH_TEXT);

        $labels = [
            'fr_FR' => 'Impression',
            'en_US' => 'Print',
        ];

        $longDescription->update(isRichRextArea: null, labels: $labels);
        $this->categoryTemplateAttributeSaver->update($longDescription);

        $longDescription = $insertedAttributes->getAttributeByCode('long_description');
        $this->assertEqualsCanonicalizing($longDescription->getLabelCollection()->getTranslations(), $labels);
    }
}
