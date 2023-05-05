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

class UpdateAttributeControllerEndToEnd extends ControllerIntegrationTestCase
{
    private TemplateUuid $templateUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('feature_flags')->enable('category_update_template_attribute');
        $this->logAs('julia');
        $this->createTemplate();
    }

    public function testItAddsAnAttributeToTheTemplate(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update_attribute',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
                'attributeUuid' => $this->templateUuid->getValue(),
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
