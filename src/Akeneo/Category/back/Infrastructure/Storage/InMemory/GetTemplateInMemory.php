<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\InMemory;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeText;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateCode;
use Akeneo\Category\Domain\ValueObject\TemplateId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemory implements GetTemplate
{
    public function byUuid(string $templateId): ?Template
    {
        $templateUuid = new TemplateId('template_uuid');

        $template = new Template(
            $templateUuid,
            new TemplateCode('template_code'),
            LabelCollection::fromArray(['fr_FR' => 'template_libelle']),
            new CategoryId(1),
            AttributeCollection::fromArray([
                AttributeText::createText(
                    new AttributeUuid('attribute_text_uuid'),
                    new AttributeCode('attribute_text_code'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_text_libelle']),
                    $templateUuid
                ),
                AttributeText::createTextArea(
                    new AttributeUuid('attribute_textarea_uuid'),
                    new AttributeCode('attribute_textarea_code'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_textarea_libelle']),
                    $templateUuid
                ),
                AttributeText::createRichText(
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

        return $template;
    }
}
