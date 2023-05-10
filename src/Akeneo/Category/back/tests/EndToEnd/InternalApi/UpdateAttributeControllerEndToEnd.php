<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAttributeControllerEndToEnd extends ControllerIntegrationTestCase
{
    private GetCategoryInterface $getCategory;
    private CategoryTemplateSaver $categoryTemplateSaver;
    private CategoryTreeTemplateSaver $categoryTreeTemplateSaver;
    private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver;
    private GetAttribute $getAttribute;

    private TemplateUuid $templateUuid;
    private AttributeCollection $attributeCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->categoryTemplateSaver = $this->get(CategoryTemplateSaver::class);
        $this->categoryTreeTemplateSaver = $this->get(CategoryTreeTemplateSaver::class);
        $this->categoryTemplateAttributeSaver = $this->get(CategoryTemplateAttributeSaver::class);
        $this->getAttribute = $this->get(GetAttribute::class);

        $this->get('feature_flags')->enable('category_update_template_attribute');
        $this->logAs('julia');
        $this->createTemplate();
    }

    public function testItUpdateAttributeTypeToRichText(): void
    {
        $longDescription = $this->attributeCollection->getAttributeByCode('text_area');
        $this->assertEquals((string) $longDescription->getType(), AttributeType::TEXTAREA);
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $longDescription->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'is_rich_text_area' => true,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $longDescription = $insertedAttributes->getAttributeByCode('text_area');
        $this->assertEquals((string) $longDescription->getType(), AttributeType::RICH_TEXT);
    }

    public function testItUpdateAttributeTypeToTextArea(): void
    {
        $richTextAttribute = $this->attributeCollection->getAttributeByCode('rich_text');
        $this->assertEquals((string) $richTextAttribute->getType(), AttributeType::RICH_TEXT);
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $richTextAttribute->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'is_rich_text_area' => false,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $richTextAttribute = $insertedAttributes->getAttributeByCode('rich_text');
        $this->assertEquals((string) $richTextAttribute->getType(), AttributeType::TEXTAREA);
    }

    public function testItThrowsErrorOnAttributeNotFound(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => '8934068e-e43f-442c-bfb4-cdd0803424e1',
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'is_rich_text_area' => false,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
    public function testItThrowsErrorOnWrongType(): void
    {
        $textAttribute = $this->attributeCollection->getAttributeByCode('text');
        $this->assertEquals((string) $textAttribute->getType(), AttributeType::TEXT);
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $textAttribute->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'is_rich_text_area' => false,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    private function createTemplate(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $this->templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $this->attributeCollection = AttributeCollection::fromArray([
            AttributeTextArea::create(
                AttributeUuid::fromString('119e55a5-d838-4b1d-80d6-2328fb6bdc97'),
                new AttributeCode('text_area'),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Long description']),
                $this->templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeRichText::create(
                AttributeUuid::fromString('e6ef21e2-d407-4414-a331-a8e83ffc29a2'),
                new AttributeCode('rich_text'),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Long description']),
                $this->templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromString('db940968-a743-44ab-b2df-1f3c853efd28'),
                new AttributeCode('text'),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Long description']),
                $this->templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            )
        ]);
        $templateModel = new Template(
            uuid: $this->templateUuid,
            code: new TemplateCode('default_template'),
            labelCollection: LabelCollection::fromArray(['en_US' => 'Default template']),
            categoryTreeId: $category->getId(),
            attributeCollection: $this->attributeCollection,
        );

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);
        $this->categoryTemplateAttributeSaver->insert($this->templateUuid, $this->attributeCollection);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
