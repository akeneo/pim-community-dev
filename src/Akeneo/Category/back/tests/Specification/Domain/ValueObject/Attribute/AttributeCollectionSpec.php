<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
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
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCollectionSpec extends ObjectBehavior
{
    public function it_retrieves_an_attribute_from_identifier(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);

        $this->beConstructedThrough(
            'fromArray',
            [
                [$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute],
            ]
        );

        $this->getAttributeByIdentifier('main_image|d049da25-5f74-43ba-b261-65ee2c9dc9f4')->shouldReturn($mainImageAttribute);
    }

    public function it_retrieves_an_attribute_from_code(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);

        $this->beConstructedThrough(
            'fromArray',
            [
                [$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute],
            ]
        );

        $this->getAttributeByCode('short_description')->shouldReturn($shortDescriptionAttribute);
    }

    public function it_adds_a_new_attribute_to_its_attributes_list(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);

        $this->beConstructedThrough(
            'fromArray',
            [
                [$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute],
            ]
        );

        $newAttribute = AttributeTextArea::create(
            AttributeUuid::fromString('f54102b9-a801-4d97-ae51-916450972c07'),
            new AttributeCode('new_attribute'),
            AttributeOrder::fromInteger(9),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(["en_US" => "New attribute"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );

        $this->addAttribute($newAttribute);
        $this->getAttributeByCode('new_attribute')->shouldReturn($newAttribute);
    }

    public function it_counts_its_number_of_attributes(): void
    {
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);

        $this->beConstructedThrough(
            'fromArray',
            [
                [$longDescriptionAttribute, $mainImageAttribute],
            ]
        );

        $this->count()->shouldReturn(2);
    }

    public function it_reindexes_its_attributes(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(30);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(50);
        $mainImageAttribute = $this->createMainImageImageAttribute(20);

        $attributeWithDuplicatedOrderIndex = AttributeText::create(
            AttributeUuid::fromString('d15245be-7d71-40e0-9638-d9f1b2bb3f5f'),
            new AttributeCode('duplicated_order'),
            AttributeOrder::fromInteger(30),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(["en_US" => "Duplicated order"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );

        $this->beConstructedThrough(
            'fromArray',
            [
                [$shortDescriptionAttribute, $longDescriptionAttribute, $attributeWithDuplicatedOrderIndex, $mainImageAttribute],
            ]
        );

        $reindexedAttributeCollection = $this->rebuildWithIndexedAttributes();

        /**
         * @var AttributeCollection $reindexedAttributeCollection
         */
        $reindexedAttributeCollection->getAttributeByCode('main_image')->getOrder()->intValue()->shouldReturn(1);
        $reindexedAttributeCollection->getAttributeByCode('short_description')->getOrder()->intValue()->shouldReturn(2);
        $reindexedAttributeCollection->getAttributeByCode('duplicated_order')->getOrder()->intValue()->shouldReturn(3);
        $reindexedAttributeCollection->getAttributeByCode('long_description')->getOrder()->intValue()->shouldReturn(4);
    }

    public function it_returns_the_potential_order_value_of_the_next_added_attribute(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(27);
        $mainImageAttribute = $this->createMainImageImageAttribute(50);

        $this->beConstructedThrough(
            'fromArray',
            [
                [$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute],
            ]
        );

        $this->count()->shouldReturn(3);
        $this->calculateNextOrder()->shouldReturn(51);
    }

    private function createShortDescriptionTextAttribute(int $order): AttributeText
    {
        return AttributeText::create(
            AttributeUuid::fromString('e30177ee-d8e8-46a4-9491-ea6c3579e727'),
            new AttributeCode('short_description'),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(["en_US" => "Short description"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
    }

    private function createLongDescriptionTextAttribute(int $order): AttributeText
    {
        return AttributeText::create(
            AttributeUuid::fromString('82afa0d1-cf51-48e0-a8d3-34444ddc1c09'),
            new AttributeCode('long_description'),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(['en_US' => "Long description"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
    }

    private function createMainImageImageAttribute(int $order): AttributeImage
    {
        return AttributeImage::create(
            AttributeUuid::fromString('d049da25-5f74-43ba-b261-65ee2c9dc9f4'),
            new AttributeCode('main_image'),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(['en_US' => "Illustration"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
    }
}
