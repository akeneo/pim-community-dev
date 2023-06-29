<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
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
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSetFactory
{
    public function createAttributeCollection(TemplateUuid $templateUuid): AttributeCollection
    {
        return AttributeCollection::fromArray([
            AttributeRichText::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
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
                AttributeUuid::fromUuid(Uuid::uuid4()),
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
                AttributeUuid::fromUuid(Uuid::uuid4()),
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
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('image_1'),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 1']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('image_alt_text_1'),
                AttributeOrder::fromInteger(5),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image alt. text 1']), // todo check casse
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeImage::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('image_2'),
                AttributeOrder::fromInteger(6),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 2']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('image_alt_text_2'),
                AttributeOrder::fromInteger(7),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image alt. text 2']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeImage::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('image_3'),
                AttributeOrder::fromInteger(8),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 3']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('image_alt_text_3'),
                AttributeOrder::fromInteger(9),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image alt. text 3']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('seo_meta_title'),
                AttributeOrder::fromInteger(10),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO meta title']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeTextArea::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(11),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('seo_h1_main_heading_tag'),
                AttributeOrder::fromInteger(12),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO H1 main heading tag']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeTextArea::create(
                AttributeUuid::fromUuid(Uuid::uuid4()),
                new AttributeCode('seo_keywords'),
                AttributeOrder::fromInteger(13),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO keywords']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
        ]);
    }
}
