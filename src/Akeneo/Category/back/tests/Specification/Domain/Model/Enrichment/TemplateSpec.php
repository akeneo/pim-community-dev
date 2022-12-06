<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\Model\Enrichment;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TemplateSpec extends ObjectBehavior
{
    public function let()
    {
        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $this->beConstructedWith(
            $templateUuid,
            new TemplateCode('template_code'),
            LabelCollection::fromArray(['fr_FR' => 'template_libelle']),
            new CategoryId(1),
            AttributeCollection::fromArray([
                AttributeText::create(
                    AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
                    new AttributeCode('attribute_text_code'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_text_libelle']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                    new AttributeCode('attribute_textarea_code'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_textarea_libelle']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeRichText::create(
                    AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                    new AttributeCode('attribute_richtext_code'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_richtext_libelle']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeImage::create(
                    AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
                    new AttributeCode('attribute_image_code'),
                    AttributeOrder::fromInteger(4),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_image_libelle']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                )
            ])
        );
    }

    function it_normalizes_template(): void
    {
        $expectedTemplateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedNormalizedTemplate = [
            'uuid' => $expectedTemplateUuid,
            'code' => 'template_code',
            'labels' => ['fr_FR' => 'template_libelle'],
            'category_tree_identifier' => 1,
            'attributes' => [
                [
                    'uuid' => '4873080d-32a3-42a7-ae5c-1be518e40f3d',
                    'code' => 'attribute_text_code',
                    'type' => 'text',
                    'order' => 1,
                    'is_required' => true,
                    'is_scopable' => true,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_text_libelle'],
                    'template_uuid' => $expectedTemplateUuid,
                    'additional_properties' => []
                ],
                [
                    'uuid' => '69e251b3-b876-48b5-9c09-92f54bfb528d',
                    'code' => 'attribute_textarea_code',
                    'type' => 'textarea',
                    'order' => 2,
                    'is_required' => true,
                    'is_scopable' => true,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_textarea_libelle'],
                    'template_uuid' => $expectedTemplateUuid,
                    'additional_properties' => []
                ],
                [
                    'uuid' => '840fcd1a-f66b-4f0c-9bbd-596629732950',
                    'code' => 'attribute_richtext_code',
                    'type' => 'richtext',
                    'order' => 3,
                    'is_required' => true,
                    'is_scopable' => true,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_richtext_libelle'],
                    'template_uuid' => $expectedTemplateUuid,
                    'additional_properties' => []
                ],
                [
                    'uuid' => '8dda490c-0fd1-4485-bdc5-342929783d9a',
                    'code' => 'attribute_image_code',
                    'type' => 'image',
                    'order' => 4,
                    'is_required' => true,
                    'is_scopable' => true,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_image_libelle'],
                    'template_uuid' => $expectedTemplateUuid,
                    'additional_properties' => []
                ]
            ]
        ];
        $this->normalize()->shouldReturn($expectedNormalizedTemplate);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Template::class);
    }
}
