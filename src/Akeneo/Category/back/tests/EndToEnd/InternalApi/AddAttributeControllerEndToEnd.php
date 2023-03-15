<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddAttributeControllerEndToEnd extends ControllerIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->logAs('julia');
    }

    public function test_it_adds_an_attribute_to_the_template(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $templateModel = new Template(
            uuid: $templateUuid,
            code: new TemplateCode('default_template'),
            labelCollection: LabelCollection::fromArray(['en_US' => 'Default template']),
            categoryTreeId: $category->getId(),
            attributeCollection: null
        );

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $data = [
            'code' => 'attribute_code',
            'type' => 'text',
            'is_scopable' => true,
            'is_localizable' => true,
            'locale' => 'en_US',
            'label' => 'The attribute'
        ];

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_add_attribute',
            routeArguments: [
                'templateUuid' => $templateUuid->getValue()
            ],
            method: Request::METHOD_POST,
            content: json_encode($data)
        );

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateUuid);
        $templateModel->setAttributeCollection($insertedAttributes);

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertNotNull($templateModel->getAttributeCollection()->getAttributeByCode('attribute_code'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
