<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateTemplateControllerEndToEnd extends ControllerIntegrationTestCase
{
    private GetCategoryInterface $getCategory;
    private GetTemplate $getTemplate;
    private CategoryTemplateSaver $categoryTemplateSaver;
    private CategoryTreeTemplateSaver $categoryTreeTemplateSaver;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->getTemplate = $this->get(GetTemplate::class);
        $this->categoryTemplateSaver = $this->get(CategoryTemplateSaver::class);
        $this->categoryTreeTemplateSaver = $this->get(CategoryTreeTemplateSaver::class);
    }

    public function testItUpdatesTemplate(): void
    {
        // Given

        $this->logAs('julia');
        $template = $this->createTemplate();

        // When

        $data = [
            'labels' => [
                'en_US' => null,
                'fr_FR' => '',
                'es_ES' => 'Una plantilla',
            ],
        ];

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update',
            routeArguments: ['templateUuid' => (string) $template->getUuid()],
            method: Request::METHOD_PATCH,
            content: json_encode($data, JSON_THROW_ON_ERROR),
        );

        // Then

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expectedLabels = [
            'en_US' => null,
            'fr_FR' => null,
            'de_DE' => 'Eine Vorlage',
            'es_ES' => 'Una plantilla',
        ];

        $updatedTemplate = $this->getTemplate->byUuid($template->getUuid());

        $this->assertEquals($expectedLabels, $updatedTemplate->normalize()['labels']);
    }

    public function testItReturnsValidationErrors(): void
    {
        // Given

        $this->logAs('julia');
        $template = $this->createTemplate();

        // When

        $data = [
            'labels' => [
                'en_US' => 'A template',
                'fr_FR' => <<<EOT
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce sed dictum diam. Aliquam auctor
                    sodales lectus, at congue massa semper non. Nunc a mollis nibh. Suspendisse potenti. Donec quis
                    lobortis justo, et consectetur turpis. Sed pulvinar lectus metus.
                EOT,
            ],
        ];

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_update',
            routeArguments: ['templateUuid' => (string) $template->getUuid()],
            method: Request::METHOD_PATCH,
            content: json_encode($data, JSON_THROW_ON_ERROR),
        );

        // Then

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $expectedErrors = [
            'labels' => [
                'fr_FR' => ['This value is too long. It should have 255 characters or less.'],
            ],
        ];

        $errors = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($expectedErrors, $errors);
    }

    private function createTemplate(): Template
    {
        /** @var Category $category */
        $category = $this->getCategory->byCode('master');

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $template = new Template(
            $templateUuid,
            new TemplateCode('default_template'),
            LabelCollection::fromArray([
                'en_US' => 'A template',
                'fr_FR' => 'Un template',
                'de_DE' => 'Eine Vorlage',
            ]),
            $category->getId(),
            null,
        );

        $this->categoryTemplateSaver->insert($template);
        $this->categoryTreeTemplateSaver->insert($template);

        return $template;
    }
}
