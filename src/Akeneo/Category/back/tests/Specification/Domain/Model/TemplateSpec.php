<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TemplateSpec extends ObjectBehavior
{
    public function let()
    {
        $templateUuid = new TemplateId('template_uuid');
        $this->beConstructedWith(
            $templateUuid,
            new TemplateCode('template_code'),
            LabelCollection::fromArray(['fr_FR' => 'template_libelle']),
            new CategoryId(1),
            AttributeCollection::fromArray([
                AttributeText::create(
                    new AttributeUuid('attribute_text_uuid'),
                    new AttributeCode('attribute_text_code'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_text_libelle']),
                    $templateUuid
                ),
                AttributeTextArea::create(
                    new AttributeUuid('attribute_textarea_uuid'),
                    new AttributeCode('attribute_textarea_code'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_textarea_libelle']),
                    $templateUuid
                ),
                AttributeRichText::create(
                    new AttributeUuid('attribute_richtext_uuid'),
                    new AttributeCode('attribute_richtext_code'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_richtext_libelle']),
                    $templateUuid
                ),
                AttributeImage::create(
                    new AttributeUuid('attribute_image_uuid'),
                    new AttributeCode('attribute_image_code'),
                    AttributeOrder::fromInteger(4),
                    AttributeIsLocalizable::fromBoolean(false),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_image_libelle']),
                    $templateUuid
                )
            ])
        );
    }

    function it_normalizes_template(): void
    {
        $expectedNormalizedTemplate = [
            'identifier' => 'template_uuid',
            'code' => 'template_code',
            'labels' => ['fr_FR' => 'template_libelle'],
            'category_tree_identifier' => 1,
            'attributes' => [
                [
                    'identifier' => 'attribute_text_uuid',
                    'code' => 'attribute_text_code',
                    'type' => 'text',
                    'order' => 1,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_text_libelle'],
                    'template_identifier' => 'template_uuid'
                ],
                [
                    'identifier' => 'attribute_textarea_uuid',
                    'code' => 'attribute_textarea_code',
                    'type' => 'textarea',
                    'order' => 2,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_textarea_libelle'],
                    'template_identifier' => 'template_uuid'
                ],
                [
                    'identifier' => 'attribute_richtext_uuid',
                    'code' => 'attribute_richtext_code',
                    'type' => 'richtext',
                    'order' => 3,
                    'is_localizable' => true,
                    'labels' => ['fr_FR' => 'attribute_richtext_libelle'],
                    'template_identifier' => 'template_uuid'
                ],
                [
                    'identifier' => 'attribute_image_uuid',
                    'code' => 'attribute_image_code',
                    'type' => 'image',
                    'order' => 4,
                    'is_localizable' => false,
                    'labels' => ['fr_FR' => 'attribute_image_libelle'],
                    'template_identifier' => 'template_uuid'
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
