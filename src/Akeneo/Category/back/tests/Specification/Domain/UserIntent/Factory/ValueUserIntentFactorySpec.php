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
use Akeneo\Category\Domain\Query\GetAttribute;
use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Category\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueUserIntentFactorySpec extends ObjectBehavior
{
    function let(GetAttribute $getAttribute)
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

    function it_creates_a_list_of_value_intent_based_on_values_field(GetAttribute $getAttribute): void
    {
        $data = [
            'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'Meta shoes',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],
            'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'Description',
                'locale' => 'en_US',
                'attribute_code' => 'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
            'color' . ValueCollection::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'red',
                'locale' => 'en_US',
                'attribute_code' => 'color' . ValueCollection::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af'
            ],
            'banner' . ValueCollection::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => [
                    'size' => 168107,
                    'extension' => 'jpg',
                    'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                    'mime_type' => 'image/jpeg',
                    'original_filename' => 'shoes.jpg'
                ],
                'locale' => 'en_US',
                'attribute_code' => 'banner' . ValueCollection::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc'
            ]
        ];

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $attributes = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(4),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                $templateUuid
            ),
            AttributeRichText::create(
                AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                new AttributeCode('description'),
                AttributeOrder::fromInteger(1),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Description']),
                $templateUuid
            ),
            AttributeText::create(
                AttributeUuid::fromString('38439aaf-66a2-4b24-854e-29d7a467c7af'),
                new AttributeCode('color'),
                AttributeOrder::fromInteger(2),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'red']),
                $templateUuid
            ),
            AttributeImage::create(
                AttributeUuid::fromString('e0326684-0dff-44be-8283-9262deb9e4bc'),
                new AttributeCode('banner'),
                AttributeOrder::fromInteger(3),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => '3/7/7/e/377e7c2bad87efd2e71eb725006a9067918d5791_banner.jpg']),
                $templateUuid
            ),
        ]);

        $identifiers = [
            'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950',
            'color' . ValueCollection::SEPARATOR . '38439aaf-66a2-4b24-854e-29d7a467c7af',
            'banner' . ValueCollection::SEPARATOR . 'e0326684-0dff-44be-8283-9262deb9e4bc'
        ];

        $getAttribute->byIdentifiers($identifiers)
            ->shouldBeCalledOnce()
            ->willReturn($attributes);

        $this->create(
            'values',
            $data
        )->shouldBeLike([
            new SetTextArea(
                '69e251b3-b876-48b5-9c09-92f54bfb528d',
                'seo_meta_description',
                'en_US',
                'Meta shoes'
            ),
            new SetRichText(
                '840fcd1a-f66b-4f0c-9bbd-596629732950',
                'description',
                'en_US',
                'Description'
            ),
            new SetText(
                '38439aaf-66a2-4b24-854e-29d7a467c7af',
                'color',
                'en_US',
                'red'
            ),
            new SetImage(
                'e0326684-0dff-44be-8283-9262deb9e4bc',
                'banner',
                'en_US',
                [
                    'size' => 168107,
                    'extension' => 'jpg',
                    'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                    'mime_type' => 'image/jpeg',
                    'original_filename' => 'shoes.jpg'
                ]
            ),
        ]);
    }

    function it_does_not_add_value_user_intent_when_corresponding_attribute_type_no_found(GetAttribute $getAttribute): void
    {
        $data = [
            'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'Meta shoes',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],
            'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'Description',
                'locale' => 'en_US',
                'attribute_code' => 'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ]
        ];

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $valueCollection = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(4),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                $templateUuid
            )
        ]);

        $identifiers = [
            'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
        ];

        $getAttribute->byIdentifiers($identifiers)
            ->shouldBeCalledOnce()
            ->willReturn($valueCollection);

        $this->create('values', $data)->shouldHaveCount(1);
    }
}
