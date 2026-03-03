<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoryTemplateAttributeSqlIntegration extends CategoryTestCase
{
    public function testGetCategoryTemplateAttributeByTemplateUuid(): void
    {
        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $expectedAttributes = $this->givenAttributes($templateUuid);

        /** @var AttributeCollection $templateCategoryAttributes */
        $templateCategoryAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateUuid);

        $this->assertCount(count($expectedAttributes), $templateCategoryAttributes);
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('long_description'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('url_slug'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('image_1'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('image_alt_text_1'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_meta_title'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_keywords'));
        // TODO : GRF-562 : Add Test on ordered attributes
    }

    public function testGetCategoryTemplateAttributeByUuids(): void
    {
        $attributeUuids = [
            AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
            AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
            AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
            AttributeUuid::fromString('783d4957-a29b-4281-a9f5-c4621014dcfa'),
            AttributeUuid::fromString('1efc3af6-e89c-4281-9bd5-b827d9397cf7'),
            AttributeUuid::fromString('4ba33f06-de92-4366-8322-991d1bad07b9'),
            // this is an unknown attribute uuid
            AttributeUuid::fromString('c45a20ae-23ad-433b-8b93-b172131688da'),
        ];

        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = $this->get(GetAttribute::class)->byUuids($attributeUuids);

        $this->assertCount(6, $attributeCollection);
        $this->assertNotNull($attributeCollection->getAttributeByCode('long_description'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('url_slug'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('image_1'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_meta_title'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_keywords'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('image_alt_text_1'));
    }

    public function testGetCategoryTemplateAttributeByUuid(): void
    {
        $attributeUuid = AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950');

        $attribute = $this->get(GetAttribute::class)->byUuid($attributeUuid);

        $this->assertNotNull($attribute);
    }

    public function testDoesNotGetCategoryDeactivatedTemplateAttributeByTemplateUuid(): void
    {
        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $longDescriptionUuid = AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950');
        $urlSlugUuid = AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d');

        $this->deactivateAttribute((string) $longDescriptionUuid);
        $this->deactivateAttribute((string) $urlSlugUuid);

        /** @var AttributeCollection $templateCategoryAttributes */
        $templateCategoryAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateUuid);

        $this->assertCount(11, $templateCategoryAttributes);
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('image_1'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_meta_title'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_keywords'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('image_alt_text_1'));
    }

    public function testDoesNotGetCategoryDeactivatedTemplateAttributeByUuids(): void
    {
        $longDescriptionUuid = AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950');
        $urlSlugUuid = AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d');
        $attributeUuids = [
            $longDescriptionUuid,
            $urlSlugUuid,
            AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
            AttributeUuid::fromString('783d4957-a29b-4281-a9f5-c4621014dcfa'),
            AttributeUuid::fromString('1efc3af6-e89c-4281-9bd5-b827d9397cf7'),
            AttributeUuid::fromString('4ba33f06-de92-4366-8322-991d1bad07b9'),
            // this is an unknown attribute uuid
            AttributeUuid::fromString('c45a20ae-23ad-433b-8b93-b172131688da'),
        ];

        $this->deactivateAttribute((string) $longDescriptionUuid);
        $this->deactivateAttribute((string) $urlSlugUuid);

        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = $this->get(GetAttribute::class)->byUuids($attributeUuids);

        $this->assertCount(4, $attributeCollection);
        $this->assertNotNull($attributeCollection->getAttributeByCode('image_1'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_meta_title'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_keywords'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('image_alt_text_1'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $productCode = 'myCategory';
        $this->useTemplateFunctionalCatalog($templateUuid, $productCode);
    }
}
