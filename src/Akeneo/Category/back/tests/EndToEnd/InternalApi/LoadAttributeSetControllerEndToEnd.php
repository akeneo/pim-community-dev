<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\AttributeSetFactory;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
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

class LoadAttributeSetControllerEndToEnd extends ControllerIntegrationTestCase
{
    private GetCategoryInterface $getCategory;
    private CategoryTemplateSaver $categoryTemplateSaver;
    private CategoryTreeTemplateSaver $categoryTreeTemplateSaver;
    private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver;
    private GetAttribute $getAttribute;
    private AttributeSetFactory $attributeSetFactory;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->categoryTemplateSaver = $this->get(CategoryTemplateSaver::class);
        $this->categoryTreeTemplateSaver = $this->get(CategoryTreeTemplateSaver::class);
        $this->categoryTemplateAttributeSaver = $this->get(CategoryTemplateAttributeSaver::class);
        $this->getAttribute = $this->get(GetAttribute::class);
        $this->attributeSetFactory = $this->get(AttributeSetFactory::class);
    }

    public function testItLoadThePredefinedAttributeSet(): void
    {
        // Given

        $this->logAs('julia');

        $template = $this->createTemplate();

        // When

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_load_attribute_set',
            routeArguments: ['templateUuid' => (string) $template->getUuid()],
            method: Request::METHOD_POST,
        );

        // Then

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expectedAttributeCollection = $this->attributeSetFactory->createAttributeCollection($template->getUuid());
        $actualAttributeCollection = $this->getAttribute->byTemplateUuid($template->getUuid());

        $this->assertAttributeCodesEquals($expectedAttributeCollection, $actualAttributeCollection);
    }

    public function testItDoesntLoadThePredefinedAttributeSetWhenTheTemplateAlreadyHaveSomeAttributes(): void
    {
        // Given

        $this->logAs('julia');

        $template = $this->createTemplate();

        $expectedAttributeCollection = AttributeCollection::fromArray([
            AttributeText::create(
                AttributeUuid::fromString('db940968-a743-44ab-b2df-1f3c853efd28'),
                new AttributeCode('url_slug'),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'URL slug']),
                $template->getUuid(),
                AttributeAdditionalProperties::fromArray([]),
            ),
        ]);

        $this->categoryTemplateAttributeSaver->insert($template->getUuid(), $expectedAttributeCollection);

        // When

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_load_attribute_set',
            routeArguments: ['templateUuid' => (string) $template->getUuid()],
            method: Request::METHOD_POST,
        );

        // Then

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $actualAttributeCollection = $this->getAttribute->byTemplateUuid($template->getUuid());

        $this->assertEquals($expectedAttributeCollection, $actualAttributeCollection);
    }

    private function createTemplate(): Template
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $template = new Template(
            $templateUuid,
            new TemplateCode('default_template'),
            LabelCollection::fromArray([]),
            $category->getId(),
            null,
        );

        $this->categoryTemplateSaver->insert($template);
        $this->categoryTreeTemplateSaver->insert($template);

        return $template;
    }

    private function assertAttributeCodesEquals(
        AttributeCollection $expectedAttributeCollection,
        AttributeCollection $actualAttributeCollection,
    ): void {
        $expectedCodes = array_map(
            fn (Attribute $attribute) => $attribute->getCode(),
            $expectedAttributeCollection->getAttributes(),
        );

        $actualCodes = array_map(
            fn (Attribute $attribute) => $attribute->getCode(),
            $actualAttributeCollection->getAttributes(),
        );

        $this->assertEquals($expectedCodes, $actualCodes);
    }
}
