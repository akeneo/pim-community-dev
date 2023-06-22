<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Controller\InternalApi\TrackUsageOfLoadPredefinedAttributesController;
use Akeneo\Test\Integration\Configuration;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUsageOfLoadPredefinedAttributesControllerEndToEnd extends ControllerIntegrationTestCase
{
    private TemplateUuid $templateUuid;
    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('category_template_customization');
        $this->logAs('julia');

        $this->createTemplate();
    }

    public function testItCallTrackUsageOfLoadPredefinedAttributes(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_track_usage_of_load_predefined_attributes',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'action' => 'load_predefined_attributes',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testItThrowsErrorOnTemplateBadAction(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_track_usage_of_load_predefined_attributes',
            routeArguments: [
                'templateUuid' => $this->templateUuid->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'action' => 'bas_action',
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testItThrowsErrorOnTemplateNotFound(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_track_usage_of_load_predefined_attributes',
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
            route: 'pim_category_template_track_usage_of_load_predefined_attributes',
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
            attributeCollection: AttributeCollection::fromArray([]),
        );

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
