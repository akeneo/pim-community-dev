<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Category\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Storage\Sql\GetCategoryTemplateAttributeSql;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueUserIntentFactorySpec extends ObjectBehavior
{
    function let(GetCategoryTemplateAttributeSql $getAttribute)
    {
        $this->beConstructedWith($getAttribute);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ValueUserIntentFactory::class);
        $this->shouldImplement(UserIntentFactory::class);
    }

    function it_manage_only_expected_field_names(): void
    {
        $this->getSupportedFieldNames()->shouldReturn(['values']);
    }

    function it_creates_a_list_of_value_intent_based_on_values_field(GetCategoryTemplateAttributeSql $getAttribute): void
    {
        $data = [
            'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => 'Meta shoes',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],
            'description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => 'Description',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
            'color' . AbstractValue::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => 'red',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'color' . AbstractValue::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af'
            ],
            'banner' . AbstractValue::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => [
                    'size' => 168107,
                    'extension' => 'jpg',
                    'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                    'mime_type' => 'image/jpeg',
                    'original_filename' => 'shoes.jpg'
                ],
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'banner' . AbstractValue::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc'
            ]
        ];

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $attributes = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
            AttributeRichText::create(
                AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                new AttributeCode('description'),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
            AttributeText::create(
                AttributeUuid::fromString('38439aaf-66a2-4b24-854e-29d7a467c7af'),
                new AttributeCode('color'),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'red']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
            AttributeImage::create(
                AttributeUuid::fromString('e0326684-0dff-44be-8283-9262deb9e4bc'),
                new AttributeCode('banner'),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => '3/7/7/e/377e7c2bad87efd2e71eb725006a9067918d5791_banner.jpg']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
        ]);

        $uuids = [
            AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
            AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
            AttributeUuid::fromString('38439aaf-66a2-4b24-854e-29d7a467c7af'),
            AttributeUuid::fromString('e0326684-0dff-44be-8283-9262deb9e4bc')
        ];

        $getAttribute->byUuids($uuids)
            ->shouldBeCalledOnce()
            ->willReturn($attributes);

        $expectedUseIntents = [
            new SetTextArea(
                '69e251b3-b876-48b5-9c09-92f54bfb528d',
                'seo_meta_description',
                'ecommerce',
                'en_US',
                'Meta shoes'
            ),
            new SetRichText(
                '840fcd1a-f66b-4f0c-9bbd-596629732950',
                'description',
                'ecommerce',
                'en_US',
                'Description'
            ),
            new SetText(
                '38439aaf-66a2-4b24-854e-29d7a467c7af',
                'color',
                'ecommerce',
                'en_US',
                'red'
            ),
            new SetImage(
                'e0326684-0dff-44be-8283-9262deb9e4bc',
                'banner',
                'ecommerce',
                'en_US',
                [
                    'size' => 168107,
                    'extension' => 'jpg',
                    'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                    'mime_type' => 'image/jpeg',
                    'original_filename' => 'shoes.jpg'
                ]
            ),
        ];
        $this->create(
            'values',
            1,
            $data
        )->shouldBeLike($expectedUseIntents);
    }

    function it_does_not_add_value_user_intent_when_corresponding_attribute_type_no_found(
        GetCategoryTemplateAttributeSql $getAttribute
    ): void {
        $data = [
            'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => 'Meta shoes',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],
            'description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => 'Description',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ]
        ];

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $valueCollection = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            )
        ]);

        $uuids = [
            AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
            AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
        ];

        $getAttribute->byUuids($uuids)
            ->shouldBeCalledOnce()
            ->willReturn($valueCollection);

        $this->create('values', 1, $data)->shouldHaveCount(1);
    }

    function it_add_value_user_intent_when_the_text_field_data_is_empty(
        GetCategoryTemplateAttributeSql $getAttribute
    ): void {
        $data = [
            'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => "",
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . AbstractValue::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],
            'description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => "<p></p>\n",
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
            'color' . AbstractValue::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => "red",
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'color' . AbstractValue::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af'
            ],
            'banner' . AbstractValue::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc' . AbstractValue::SEPARATOR . 'ecommerce' . AbstractValue::SEPARATOR . 'en_US' => [
                'data' => null,
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'banner' . AbstractValue::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc'
            ]
        ];

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $attributes = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
            AttributeRichText::create(
                AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                new AttributeCode('description'),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
            AttributeText::create(
                AttributeUuid::fromString('38439aaf-66a2-4b24-854e-29d7a467c7af'),
                new AttributeCode('color'),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'red']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
            AttributeImage::create(
                AttributeUuid::fromString('e0326684-0dff-44be-8283-9262deb9e4bc'),
                new AttributeCode('banner'),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => '3/7/7/e/377e7c2bad87efd2e71eb725006a9067918d5791_banner.jpg']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([])
            ),
        ]);

        $uuids = [
            AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
            AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
            AttributeUuid::fromString('38439aaf-66a2-4b24-854e-29d7a467c7af'),
            AttributeUuid::fromString('e0326684-0dff-44be-8283-9262deb9e4bc')
        ];

        $getAttribute->byUuids($uuids)
            ->shouldBeCalledOnce()
            ->willReturn($attributes);

        $expectedUseIntents = [
            new SetTextArea(
                '69e251b3-b876-48b5-9c09-92f54bfb528d',
                'seo_meta_description',
                'ecommerce',
                'en_US',
                null
            ),
            new SetRichText(
                '840fcd1a-f66b-4f0c-9bbd-596629732950',
                'description',
                'ecommerce',
                'en_US',
                null
            ),
            new SetText(
                '38439aaf-66a2-4b24-854e-29d7a467c7af',
                'color',
                'ecommerce',
                'en_US',
                'red'
            ),
            new SetImage(
                'e0326684-0dff-44be-8283-9262deb9e4bc',
                'banner',
                'ecommerce',
                'en_US',
                null
            ),
        ];
        $this->create(
            'values',
            1,
            $data
        )->shouldBeLike($expectedUseIntents);
    }
}
