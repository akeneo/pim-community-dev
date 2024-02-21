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
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeactivateAttributeControllerEndToEnd extends ControllerIntegrationTestCase
{
    private TemplateUuid $templateUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logAs('julia');
        $this->createTemplate();
    }

    public function testItDeactivateAttributesFromTheTemplate(): void
    {
        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);

        $this->assertCount(13, $insertedAttributes);

        foreach (range(0, 2) as $index) {
            $attributeUuid = $insertedAttributes->getAttributes()[$index]->getUuid();
            $this->callApiRoute(
                client: $this->client,
                route: 'pim_category_template_rest_delete_attribute',
                routeArguments: [
                    'templateUuid' => $this->templateUuid->getValue(),
                    'attributeUuid' => $attributeUuid->getValue(),
                ],
                method: Request::METHOD_DELETE,
            );

            $response = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        }

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid);
        $this->assertCount(10, $insertedAttributes);
    }

    public function testItThrowsExceptionsWhenUuidInvalid(): void
    {
        $this->callApiRoute(
            client: $this->client,
            route: 'pim_category_template_rest_delete_attribute',
            routeArguments: [
                'templateUuid' => 'invalid',
                'attributeUuid' => 'invalid',
            ],
            method: Request::METHOD_DELETE,
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
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

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection(),
        );
    }

    protected function givenAttributes(TemplateUuid $templateUuid): AttributeCollection
    {
        $uuids = [
            '840fcd1a-f66b-4f0c-9bbd-596629732950',
            '8dda490c-0fd1-4485-bdc5-342929783d9a',
            '4873080d-32a3-42a7-ae5c-1be518e40f3d',
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            '4ba33f06-de92-4366-8322-991d1bad07b9',
            '47c8dfb1-bf7b-4397-914e-65208dd51051',
            '804cddcf-bacd-43c4-8494-b3ccb51e04cc',
            '75ec2c1f-56ea-4db1-82c4-4efe070afccf',
            'b72b7414-082b-4e1e-a98f-3a04ac8193bc',
            '783d4957-a29b-4281-a9f5-c4621014dcfa',
            'b777dfe6-2518-4d0e-958d-ddb07c81b7b6',
            '7898eab7-c795-4989-8583-54974563e1b7',
            '1efc3af6-e89c-4281-9bd5-b827d9397cf7',
        ];

        return AttributeCollection::fromArray([
            AttributeRichText::create(
                AttributeUuid::fromString($uuids[0]),
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
                AttributeUuid::fromString($uuids[1]),
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
                AttributeUuid::fromString($uuids[2]),
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
                AttributeUuid::fromString($uuids[3]),
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
                AttributeUuid::fromString($uuids[4]),
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
                AttributeUuid::fromString($uuids[5]),
                new AttributeCode('image_2'),
                AttributeOrder::fromInteger(6),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 2']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromString($uuids[6]),
                new AttributeCode('image_alt_text_2'),
                AttributeOrder::fromInteger(7),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image alt. text 2']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeImage::create(
                AttributeUuid::fromString($uuids[7]),
                new AttributeCode('image_3'),
                AttributeOrder::fromInteger(8),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image 3']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromString($uuids[8]),
                new AttributeCode('image_alt_text_3'),
                AttributeOrder::fromInteger(9),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'Image alt. text 3']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromString($uuids[9]),
                new AttributeCode('seo_meta_title'),
                AttributeOrder::fromInteger(10),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO meta title']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeTextArea::create(
                AttributeUuid::fromString($uuids[10]),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(11),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeText::create(
                AttributeUuid::fromString($uuids[11]),
                new AttributeCode('seo_h1_main_heading_tag'),
                AttributeOrder::fromInteger(12),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO H1 main heading tag']),
                $templateUuid,
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeTextArea::create(
                AttributeUuid::fromString($uuids[12]),
                new AttributeCode('seo_keywords'),
                AttributeOrder::fromInteger(13),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO keywords']),
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
