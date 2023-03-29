<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextAreaValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateCategoryControllerEndToEnd extends ControllerIntegrationTestCase
{
    private ?CategoryId $categoryID;

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('enriched_category');
        $this->logAs('julia');
        $this->createCategory(
            'jeans',
            'master',
        );
    }

    public function testItUpdatesCategoryWithLabels(): void
    {
        /**
         * @var GetCategoryInterface $getCategory
         */
        $getCategory = $this->get(GetCategoryInterface::class);

        /**
         * @var Category $category
         */
        $category = $getCategory->byId($this->categoryID->getValue());

        $this->assertEmpty($category->getLabels()->getTranslations());

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_enriched_category_rest_update',
            routeArguments: [
                'id' => (string) $this->categoryID->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'id' => $category->getId()->getValue(),
                'parent' => $category->getParentId()?->getValue(),
                'root_id' => $category->getRootId()?->getValue(),
                'template_uuid' => null,
                'properties' => [
                    'code' => $category->getCode(),
                    'labels' => [
                        'de_DE' => 'Hose',
                        'en_US' => 'Pants',
                        'fr_FR' => 'Pantalon',
                    ],
                ],
                'attributes' => [],
                'permissions' => [],
                'isRoot' => $category->isRoot(),
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedCategory = $getCategory->byId($this->categoryID->getValue());
        $this->assertNotEmpty($insertedCategory->getLabels()->getTranslations());
        $this->assertEquals('Hose', $insertedCategory->getLabels()->getTranslation('de_DE'));
        $this->assertEquals('Pants', $insertedCategory->getLabels()->getTranslation('en_US'));
        $this->assertEquals('Pantalon', $insertedCategory->getLabels()->getTranslation('fr_FR'));
    }

    public function testItUpdatesCategoryWithAttributes(): void
    {
        /**
         * @var GetCategoryInterface $getCategory
         */
        $getCategory = $this->get(GetCategoryInterface::class);

        /**
         * @var Category $categoryMaster
         */
        $categoryMaster = $getCategory->byCode('master');

        $templateUuid = $this->activateTemplate($categoryMaster->getId(), TemplateCode::fromString((string) $categoryMaster->getCode()), []);

        /**
         * @var GetAttribute $getAttribute
         */
        $getAttribute = $this->get(GetAttribute::class);

        /**
         * @var AttributeCollection $attributes
         */
        $attributes = $getAttribute->byTemplateUuid($templateUuid);
        $longDescriptionAttribute = $attributes->getAttributeByCode('long_description');
        $shortDescriptionAttribute = $attributes->getAttributeByCode('short_description');
        $longDescriptionAttributeCode = "{$longDescriptionAttribute->getCode()}|{$longDescriptionAttribute->getUuid()}";
        $shortDescriptionAttributeCode = "{$shortDescriptionAttribute->getCode()}|{$shortDescriptionAttribute->getUuid()}";

        /**
         * @var Category $category
         */
        $category = $getCategory->byId($this->categoryID->getValue());

        $this->assertEmpty($category->getAttributes());

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_enriched_category_rest_update',
            routeArguments: [
                'id' => (string) $this->categoryID->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'id' => $category->getId()->getValue(),
                'parent' => $category->getParentId()?->getValue(),
                'root_id' => $category->getRootId()?->getValue(),
                'template_uuid' => $templateUuid,
                'properties' => [
                    'code' => $category->getCode(),
                    'labels' => [],
                ],
                'attributes' => [
                    "$shortDescriptionAttributeCode|ecommerce|en_US" => [
                        'data' => '<p>Short description</p>\n',
                        'channel' => 'ecommerce',
                        'locale' => 'en_US',
                        'attribute_code' => "$shortDescriptionAttributeCode",
                    ],
                    "$longDescriptionAttributeCode|ecommerce|en_US" => [
                        'data' => '<p>Long description</p>\n',
                        'channel' => 'ecommerce',
                        'locale' => 'en_US',
                        'attribute_code' => "$longDescriptionAttributeCode",
                    ],
                ],
                'permissions' => [],
                'isRoot' => $category->isRoot(),
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $insertedCategory = $getCategory->byId($this->categoryID->getValue());
        $this->assertNotEmpty($insertedCategory->getAttributes());
        $shortDescriptionInserted = $insertedCategory->getAttributes()->getValue('short_description', (string) $shortDescriptionAttribute->getUuid(), 'ecommerce', 'en_US');
        $longDescriptionInserted = $insertedCategory->getAttributes()->getValue('long_description', (string) $longDescriptionAttribute->getUuid(), 'ecommerce', 'en_US');
        $this->assertInstanceOf(TextAreaValue::class, $shortDescriptionInserted);
        $this->assertInstanceOf(TextAreaValue::class, $longDescriptionInserted);
        $this->assertEquals('<p>Short description</p>\n', $shortDescriptionInserted->getValue());
        $this->assertEquals('<p>Long description</p>\n', $longDescriptionInserted->getValue());
    }

    public function testItThrowsExceptionsOnCodeNotExists(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_enriched_category_rest_update',
            routeArguments: [
                'id' => '999999999',
            ],
            method: Request::METHOD_POST,
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testItThrowsExceptionsOnIncoherentContent(): void
    {
        /**
         * @var GetCategoryInterface $getCategory
         */
        $getCategory = $this->get(GetCategoryInterface::class);

        /**
         * @var Category $category
         */
        $category = $getCategory->byId($this->categoryID->getValue());

        $this->callApiRoute(
            client: $this->client,
            route: 'pim_enriched_category_rest_update',
            routeArguments: [
                'id' => (string) $category->getId()->getValue(),
            ],
            method: Request::METHOD_POST,
            content: json_encode([
                'id' => 999999999,
                'parent' => 9999999999,
                'root_id' => 99999999999,
                'template_uuid' => null,
                'properties' => [
                    'code' => null,
                    'labels' => [],
                ],
                'attributes' => [],
                'permissions' => [],
                'isRoot' => true,
            ]),
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    protected function createCategory(?string $code, ?string $parent): void
    {
        /**
         * @var GetCategoryInterface $getCategory
         */
        $getCategory = $this->get(GetCategoryInterface::class);

        /**
         * @var Category $categoryMaster
         */
        $categoryMaster = $getCategory->byCode($parent);

        /** @var Category $category */
        $category = new Category(
            id: null,
            code: new Code($code),
            templateUuid: null,
            parentId: $categoryMaster->getId(),
            parentCode: new Code($parent),
            rootId: $categoryMaster->getId(),
        );
        $this->get(CategoryBaseSaver::class)->save($category);

        /**
         * @var Category $insertedCategory
         */
        $insertedCategory = $getCategory->byCode($code);

        $this->categoryID = $insertedCategory->getId();
    }

    /**
     * @param array<string> $labels
     */
    protected function activateTemplate(CategoryId $categoryTreeId, TemplateCode $code, ?array $labels): ?TemplateUuid
    {
        /**
         * @var ActivateTemplate $activateTemplateService
         */
        $activateTemplateService = $this->get(ActivateTemplate::class);

        try {
            return ($activateTemplateService)(
                $categoryTreeId,
                $code,
                LabelCollection::fromArray($labels)
            );
        } catch (Exception|\Doctrine\DBAL\Exception $e) {
            $this->fail('An unexpected exception was thrown: '.$e->getMessage());
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
