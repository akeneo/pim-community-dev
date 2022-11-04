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
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('description'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('banner_image'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_meta_title'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_meta_description'));
        $this->assertNotNull($templateCategoryAttributes->getAttributeByCode('seo_keywords'));
        // TODO : GRF-562 : Add Test on ordered attributes
    }

    public function testGetCategoryTemplateAttributeByUuid(): void
    {
        $attributeUuids = [
            AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
            AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
            AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
            AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
            AttributeUuid::fromString('4ba33f06-de92-4366-8322-991d1bad07b9')
        ];

        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = $this->get(GetAttribute::class)->byUuids($attributeUuids);

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $expectedAttributes = $this->givenAttributes($templateUuid);

        $this->assertCount(count($expectedAttributes), $attributeCollection);
        $this->assertNotNull($attributeCollection->getAttributeByCode('description'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('banner_image'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_meta_title'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_meta_description'));
        $this->assertNotNull($attributeCollection->getAttributeByCode('seo_keywords'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $productCode = "myCategory";
        $this->useTemplateFunctionalCatalog($templateUuid, $productCode);
    }
}
