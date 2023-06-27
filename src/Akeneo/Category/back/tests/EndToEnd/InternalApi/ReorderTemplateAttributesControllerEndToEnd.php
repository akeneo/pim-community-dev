<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
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
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReorderTemplateAttributesControllerEndToEnd extends ControllerIntegrationTestCase
{
    private GetAttribute $getAttribute;
    private CategoryTemplateSaver $categoryTemplateSaver;
    private CategoryTreeTemplateSaver $categoryTreeTemplateSaver;
    private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver;

    private TemplateUuid $templateUuid;
    /** @var array<string> */
    private array $attributeUuids = [
        '840fcd1a-f66b-4f0c-9bbd-596629732950',
        '8dda490c-0fd1-4485-bdc5-342929783d9a',
        '4873080d-32a3-42a7-ae5c-1be518e40f3d',
        '69e251b3-b876-48b5-9c09-92f54bfb528d',
        '4ba33f06-de92-4366-8322-991d1bad07b9',
        '47c8dfb1-bf7b-4397-914e-65208dd51051',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAttribute = $this->get(GetAttribute::class);
        $this->categoryTemplateSaver = $this->get(CategoryTemplateSaver::class);
        $this->categoryTreeTemplateSaver = $this->get(CategoryTreeTemplateSaver::class);
        $this->categoryTemplateAttributeSaver = $this->get(CategoryTemplateAttributeSaver::class);

        $this->get('feature_flags')->enable('category_template_customization');
        $this->logAs('julia');

        $this->createTemplate();
    }

    public function testItReorderTemplateAttributes(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_reorder_attributes',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                $this->attributeUuids[0],
                $this->attributeUuids[3],
                $this->attributeUuids[4],
                $this->attributeUuids[5],
                $this->attributeUuids[1],
                $this->attributeUuids[2],
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $reorderedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $reorderedAttributesUuid = array_map(fn ($attribute) => $attribute->getUuid()->getValue(), $reorderedAttributes->getAttributes());

        $expectedAttributesOrder = [
            $this->attributeUuids[0],
            $this->attributeUuids[3],
            $this->attributeUuids[4],
            $this->attributeUuids[5],
            $this->attributeUuids[1],
            $this->attributeUuids[2],
        ];
        $this->assertSame($expectedAttributesOrder, $reorderedAttributesUuid);
    }

    public function testItThrowsErrorOnTemplateNotFound(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_reorder_attributes',
            routeArguments: [
                'templateUuid' => '885ee073-046e-4b46-864d-9d22532e69c5',
            ],
            method: Request::METHOD_POST,
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testItThrowsExceptionsWhenUuidInvalid(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_reorder_attributes',
            routeArguments: [
                'templateUuid' => 'invalid',
            ],
            method: Request::METHOD_DELETE,
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    private function createTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $this->templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $templateModel = new Template(
            uuid: $this->templateUuid,
            code: new TemplateCode('default_template'),
            labelCollection: LabelCollection::fromArray(['en_US' => 'Default template']),
            categoryTreeId: $category->getId(),
            attributeCollection: $this->givenAttributes($this->templateUuid),
        );

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);
        $this->categoryTemplateAttributeSaver->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection(),
        );
    }

    protected function givenAttributes(TemplateUuid $templateUuid): AttributeCollection
    {
        return AttributeCollection::fromArray([
            AttributeRichText::create(
                AttributeUuid::fromString($this->attributeUuids[0]),
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
                AttributeUuid::fromString($this->attributeUuids[1]),
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
                AttributeUuid::fromString($this->attributeUuids[2]),
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
                AttributeUuid::fromString($this->attributeUuids[3]),
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
                AttributeUuid::fromString($this->attributeUuids[4]),
                new AttributeCode('image_alt_text_1'),
                AttributeOrder::fromInteger(5),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image alt. text 1']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeImage::create(
                AttributeUuid::fromString($this->attributeUuids[5]),
                new AttributeCode('image_2'),
                AttributeOrder::fromInteger(6),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 2']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
