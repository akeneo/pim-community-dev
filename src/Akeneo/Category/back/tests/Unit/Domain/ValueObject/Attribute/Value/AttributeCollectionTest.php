<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
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

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCollectionTest extends CategoryTestCase
{
    public function testReorder(): void
    {
        // Given
        $attributeCollection = $this->givenAttributeCollection();

        $orderedUuids = [
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            '8dda490c-0fd1-4485-bdc5-342929783d9a',
            '840fcd1a-f66b-4f0c-9bbd-596629732950',
            'unknown-uuid',
            ];

        // When
        $attributeCollection->reorder($orderedUuids);

        // Then
        $this->assertEquals(3, $attributeCollection->getAttributeByUuid('840fcd1a-f66b-4f0c-9bbd-596629732950')->getOrder()->intValue());
        $this->assertEquals(2, $attributeCollection->getAttributeByUuid('8dda490c-0fd1-4485-bdc5-342929783d9a')->getOrder()->intValue());
        $this->assertEquals(4, $attributeCollection->getAttributeByUuid('4873080d-32a3-42a7-ae5c-1be518e40f3d')->getOrder()->intValue());
        $this->assertEquals(1, $attributeCollection->getAttributeByUuid('69e251b3-b876-48b5-9c09-92f54bfb528d')->getOrder()->intValue());
    }

    private function givenAttributeCollection(): AttributeCollection
    {
        $templateUuid = TemplateUuid::fromString('804c35bd-4353-438b-9455-6e7b5e9c24f2');
        $attributeUuids = [
            '840fcd1a-f66b-4f0c-9bbd-596629732950',
            '8dda490c-0fd1-4485-bdc5-342929783d9a',
            '4873080d-32a3-42a7-ae5c-1be518e40f3d',
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
        ];

        return AttributeCollection::fromArray([
            AttributeRichText::create(
                AttributeUuid::fromString($attributeUuids[0]),
                new AttributeCode('long_description'),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Long description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeRichText::create(
                AttributeUuid::fromString($attributeUuids[1]),
                new AttributeCode('short_description'),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Short description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromString($attributeUuids[2]),
                new AttributeCode('url_slug'),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'URL slug']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeImage::create(
                AttributeUuid::fromString($attributeUuids[3]),
                new AttributeCode('image_1'),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 1']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
        ]);
    }
}
