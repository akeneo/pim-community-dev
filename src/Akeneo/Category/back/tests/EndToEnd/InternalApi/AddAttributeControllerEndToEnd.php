<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
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

class AddAttributeControllerEndToEnd extends ControllerIntegrationTestCase
{
    private TemplateUuid $templateUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logAs('julia');
        $this->createTemplate();
    }

    public function testItAddsAnAttributeToTheTemplate(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => 'attribute_code',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => 'en_US',
                'label' => 'The attribute',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertNotNull($insertedAttributes->getAttributeByCode('attribute_code'));
    }

    public function testItThrowsExceptionsOnNotBlankValues(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => '',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => '',
                'label' => 'The attribute',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $normalizedErrors = json_decode($response->getContent(), true);
        $this->assertEquals('code', $normalizedErrors[0]['error']['property']);
        $this->assertEquals('This value should not be blank.', $normalizedErrors[0]['error']['message']);

        $this->assertEquals('locale', $normalizedErrors[1]['error']['property']);
        $this->assertEquals('This value should not be blank.', $normalizedErrors[1]['error']['message']);

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertEmpty($insertedAttributes->getAttributes());
    }

    public function testItThrowsExceptionsOnTooLongValues(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => 'attribute_code_attribute_code_attribute_code_attribute_code_attribute_code_attribute_code_attribute_code',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => 'en_US',
                'label' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
                In consectetur magna at magna consequat lacinia. Ut dapibus nulla sit amet nibh mattis aliquet. 
                In nec arcu eros. Suspendisse potenti. Etiam sagittis, diam sed commodo vehicula, libero mi mollis est.',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $normalizedErrors = json_decode($response->getContent(), true);
        $this->assertEquals('code', $normalizedErrors[0]['error']['property']);
        $this->assertEquals('This value is too long. It should have 100 characters or less.', $normalizedErrors[0]['error']['message']);

        $this->assertEquals('label', $normalizedErrors[1]['error']['property']);
        $this->assertEquals('This value is too long. It should have 255 characters or less.', $normalizedErrors[1]['error']['message']);

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertEmpty($insertedAttributes->getAttributes());
    }

    public function testItThrowsExceptionsOnWrongFormatValues(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => 'Attribute code',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => 'en_US',
                'label' => 'The attribute',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $normalizedErrors = json_decode($response->getContent(), true);
        $this->assertEquals('code', $normalizedErrors[0]['error']['property']);
        $this->assertEquals('Attribute code may contain only lowercase letters, numbers and underscores', $normalizedErrors[0]['error']['message']);

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertEmpty($insertedAttributes->getAttributes());
    }

    public function testItThrowsExceptionsOnIdenticalCodesInTheTemplate(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => 'same_attribute_code',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => 'en_US',
                'label' => 'The attribute 1',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertNotNull($insertedAttributes->getAttributeByCode('same_attribute_code'));

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => 'same_attribute_code',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => 'en_US',
                'label' => 'The attribute 2',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $normalizedErrors = json_decode($response->getContent(), true);
        $this->assertEquals('code', $normalizedErrors[0]['error']['property']);
        $this->assertEquals('Attribute code same_attribute_code must be unique in the template', $normalizedErrors[0]['error']['message']);

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertCount(1, $insertedAttributes->getAttributes());
    }

    public function testItThrowsExceptionsWhenTheLimitOfAttributesInTheTemplateIsReached(): void
    {
        $attributeCollection = AttributeCollection::fromArray([]);
        for ($i = 0; $i < 50; ++$i) {
            $attributeCollection->addAttribute(
                Attribute::fromType(
                    type: new AttributeType(AttributeType::TEXT),
                    uuid: AttributeUuid::fromUuid(Uuid::uuid4()),
                    code: new AttributeCode('attribute_code'.$i),
                    order: AttributeOrder::fromInteger($i),
                    isRequired: AttributeIsRequired::fromBoolean(false),
                    isScopable: AttributeIsScopable::fromBoolean(true),
                    isLocalizable: AttributeIsLocalizable::fromBoolean(true),
                    labelCollection: LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                    templateUuid: $this->templateUuid,
                    additionalProperties: AttributeAdditionalProperties::fromArray([]),
                ),
            );
        }

        $this->get(CategoryTemplateAttributeSaver::class)->insert($this->templateUuid, $attributeCollection);

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'code' => 'attribute_code',
                'type' => 'text',
                'is_scopable' => true,
                'is_localizable' => true,
                'locale' => 'en_US',
                'label' => 'The attribute',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $normalizedErrors = json_decode($response->getContent(), true);
        $this->assertEquals('You cannot add more than 50 attributes', $normalizedErrors[0]['error']['message']);
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
            attributeCollection: null,
        );

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
