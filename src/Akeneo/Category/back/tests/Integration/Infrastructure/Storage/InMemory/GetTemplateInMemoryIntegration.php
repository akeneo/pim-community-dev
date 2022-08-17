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
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemoryIntegration extends TestCase
{
    public function testGetTemplateById(): void
    {
        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
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
        $templateUuid = TemplateUuid::fromString($templateUuid);

        return new Template(
            $templateUuid,
            new TemplateCode('template_code'),
            LabelCollection::fromArray(['fr_FR' => 'template_libelle']),
            new CategoryId(1),
            AttributeCollection::fromArray([
                AttributeText::create(
                    AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
                    new AttributeCode('attribute_text_code'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_text_libelle']),
                    $templateUuid
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                    new AttributeCode('attribute_textarea_code'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_textarea_libelle']),
                    $templateUuid
                ),
                AttributeRichText::create(
                    AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                    new AttributeCode('attribute_richtext_code'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['fr_FR' => 'attribute_richtext_libelle']),
                    $templateUuid
                ),
                AttributeImage::create(
                    AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
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
