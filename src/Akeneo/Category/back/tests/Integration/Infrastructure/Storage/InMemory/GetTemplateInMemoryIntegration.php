<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\InMemory;

use Akeneo\Category\Application\Query\GetTemplate;
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
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemoryIntegration extends TestCase
{
    public function testGetTemplateById(): void
    {
        $templateUuid = 'template_uuid';
        $expectedTemplate = $this->givenTemplate($templateUuid);
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        $this->assertEquals($expectedTemplate, $template);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenTemplate(string $templateUuid): Template
    {
        $templateUuid = new TemplateId($templateUuid);

        return new Template(
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
}
