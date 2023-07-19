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
use Ramsey\Uuid\Uuid;
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
    private AttributeTextArea $attributeTextArea;
    private AttributeRichText $attributeRichText;
    private AttributeText $attributeText;

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

    public function testItUpdatesAttributeTypeToRichText(): void
    {
        $this->assertEquals(AttributeType::TEXTAREA, (string) $this->attributeTextArea->getType());
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeTextArea->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'isRichTextArea' => true,
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $textArea = $insertedAttributes->getAttributeByCode('text_area');
        $this->assertEquals(AttributeType::RICH_TEXT, (string) $textArea->getType());
    }

    public function testItUpdatesAttributeTypeToTextArea(): void
    {
        $this->assertEquals(AttributeType::RICH_TEXT, (string) $this->attributeRichText->getType());
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeRichText->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'isRichTextArea' => false,
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $richTextAttribute = $insertedAttributes->getAttributeByCode('rich_text');
        $this->assertEquals(AttributeType::TEXTAREA, (string) $richTextAttribute->getType());
    }

    public function testItAddsLabelsToAttribute(): void
    {
        $labels = [
            'fr_FR' => 'Impression',
            'en_US' => 'Print',
        ];

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeRichText->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode(['labels' => $labels], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $richTextAttribute = $insertedAttributes->getAttributeByCode('rich_text');
        $this->assertEqualsCanonicalizing($richTextAttribute->getLabelCollection()->getTranslations(), $labels);
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
                'isRichTextArea' => false,
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testItThrowsErrorOnWrongType(): void
    {
        $this->assertEquals((string) $this->attributeText->getType(), AttributeType::TEXT);
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeText->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'isRichTextArea' => false,
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testItThrowsErrorOnLabelTooLong(): void
    {
        $labels = [
            'fr_FR' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam pharetra arcu at nisl malesuada feugiat. Mauris aliquam congue interdum. Etiam varius vestibulum rutrum. Pellentesque fermentum, tortor eu posuere tincidunt, libero ex consectetur arcu fusce.',
            'en_US' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam pharetra arcu at nisl malesuada feugiat. Mauris aliquam congue interdum. Etiam varius vestibulum rutrum. Pellentesque fermentum, tortor eu posuere tincidunt, libero ex consectetur arcu fusce.',
        ];

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeRichText->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode(['labels' => $labels], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $expectedErrors = [
            'labels' => [
                'fr_FR' => ['This value is too long. It should have 255 characters or less.'],
                'en_US' => ['This value is too long. It should have 255 characters or less.'],
            ],
        ];
        $normalizedErrors = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($expectedErrors, $normalizedErrors);
    }

    public function testItDoesNotUpdateOnDeactivateTemplate(): void
    {
        $this->deactivateTemplate($this->templateUuid->getValue());
        $this->assertEquals(AttributeType::RICH_TEXT, (string) $this->attributeRichText->getType());
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeRichText->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'isRichTextArea' => false,
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->getAttribute->byTemplateUuid($this->templateUuid);
        $richTextAttribute = $insertedAttributes->getAttributeByCode('rich_text');
        $this->assertEquals(AttributeType::RICH_TEXT, (string) $richTextAttribute->getType());
    }

    public function testItDoesNotUpdateOnDeactivateAttribute(): void
    {
        $this->deactivateAttribute($this->attributeRichText->getUuid()->getValue());
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->attributeRichText->getUuid()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'isRichTextArea' => false,
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    private function createTemplate(): void
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $this->templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $this->attributeTextArea = AttributeTextArea::create(
            AttributeUuid::fromString('119e55a5-d838-4b1d-80d6-2328fb6bdc97'),
            new AttributeCode('text_area'),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            $this->templateUuid,
            AttributeAdditionalProperties::fromArray([]),
        );

        $this->attributeRichText = AttributeRichText::create(
            AttributeUuid::fromString('e6ef21e2-d407-4414-a331-a8e83ffc29a2'),
            new AttributeCode('rich_text'),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            $this->templateUuid,
            AttributeAdditionalProperties::fromArray([]),
        );

        $this->attributeText = AttributeText::create(
            AttributeUuid::fromString('db940968-a743-44ab-b2df-1f3c853efd28'),
            new AttributeCode('text'),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            $this->templateUuid,
            AttributeAdditionalProperties::fromArray([]),
        );

        $attributeCollection = AttributeCollection::fromArray([
            $this->attributeTextArea,
            $this->attributeRichText,
            $this->attributeText,
        ]);
        $templateModel = new Template(
            uuid: $this->templateUuid,
            code: new TemplateCode('default_template'),
            labelCollection: LabelCollection::fromArray(['en_US' => 'Default template']),
            categoryTreeId: $category->getId(),
            attributeCollection: $attributeCollection,
        );

        $this->categoryTemplateSaver->insert($templateModel);
        $this->categoryTreeTemplateSaver->insert($templateModel);
        $this->categoryTemplateAttributeSaver->insert($this->templateUuid, $attributeCollection);
    }

    protected function deactivateTemplate(string $uuid): void
    {
        $query = <<<SQL
            UPDATE pim_catalog_category_template SET is_deactivated = 1 WHERE uuid = :uuid;
        SQL;

        $this->get('database_connection')->executeQuery($query, [
            'uuid' => Uuid::fromString($uuid)->getBytes(),
        ]);
    }

    protected function deactivateAttribute(string $uuid): void
    {
        $query = <<<SQL
            UPDATE pim_catalog_category_attribute SET is_deactivated = 1 WHERE uuid = :uuid;
        SQL;

        $this->get('database_connection')->executeQuery($query, [
            'uuid' => Uuid::fromString($uuid)->getBytes(),
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
