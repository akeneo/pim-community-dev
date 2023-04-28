<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\EndToEnd\ExternalApi;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Storage\InMemory\GetTemplateInMemory;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Ramsey\Uuid\Uuid;

abstract class ApiCategoryTestCase extends ApiTestCase
{
    protected function enableEnrichedCategoryFeature(): void
    {
        $this->get('feature_flags')->enable('enriched_category');
    }

    /**
     * @param array<string, string>|null $templateLabels
     * @param array<array<string, mixed>>|null $templateAttributes
     *
     * @throws \Exception
     */
    public function generateMockedCategoryTemplateModel(
        ?string $templateUuid = null,
        ?string $templateCode = null,
        ?array $templateLabels = null,
        ?int $categoryTreeId = null,
        ?array $templateAttributes = null,
    ): Template {
        $getTemplate = new GetTemplateInMemory();
        $generatedTemplateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        /** @var Template $defaultTemplate */
        $defaultTemplate = $getTemplate->byUuid($generatedTemplateUuid);

        if ($templateUuid === null) {
            $templateUuid = $defaultTemplate->getUuid();
        } else {
            $templateUuid = TemplateUuid::fromString($templateUuid);
        }

        if ($templateCode === null) {
            $templateCode = $defaultTemplate->getCode();
        } else {
            $templateCode = new TemplateCode($templateCode);
        }

        if ($templateLabels === null) {
            $templateLabels = $defaultTemplate->getLabelCollection();
        } else {
            $templateLabels = LabelCollection::fromArray($templateLabels);
        }

        if ($categoryTreeId === null) {
            $categoryTreeId = $defaultTemplate->getCategoryTreeId();
        } else {
            $categoryTreeId = new CategoryId($categoryTreeId);
        }

        if ($templateAttributes === null) {
            $templateAttributes = $this->givenAttributes($generatedTemplateUuid);
        } else {
            $attributes = [];
            foreach ($templateAttributes as $attribute) {
                if (!array_key_exists('type', $attribute)) {
                    throw new \Exception('Can\'t generate mocked template: missing attribute type');
                }

                switch ($attribute['type']) {
                    case AttributeType::TEXTAREA:
                        $attributeClass = AttributeTextArea::class;
                        break;
                    case AttributeType::TEXT:
                        $attributeClass = AttributeText::class;
                        break;
                    case AttributeType::IMAGE:
                        $attributeClass = AttributeImage::class;
                        break;
                    case AttributeType::RICH_TEXT:
                        $attributeClass = AttributeRichText::class;
                        break;
                    default:
                        throw new \Exception(sprintf('Can\'t generate mocked template: unknown attribute type "%s"', $attribute['type']));
                }

                $attributes[] = $attributeClass::create(
                    AttributeUuid::fromString($attribute['uuid']),
                    new AttributeCode($attribute['code']),
                    AttributeOrder::fromInteger((int) $attribute['order']),
                    AttributeIsRequired::fromBoolean((bool) $attribute['is_required']),
                    AttributeIsScopable::fromBoolean((bool) $attribute['is_scopable']),
                    AttributeIsLocalizable::fromBoolean((bool) $attribute['is_localizable']),
                    LabelCollection::fromArray($attribute['labels']),
                    TemplateUuid::fromString($attribute['template_uuid']),
                    AttributeAdditionalProperties::fromArray($attribute['additional_properties']),
                );
            }
            $templateAttributes = AttributeCollection::fromArray($attributes);
        }

        return new Template(
            $templateUuid,
            $templateCode,
            $templateLabels,
            $categoryTreeId,
            $templateAttributes,
        );
    }

    protected function updateCategoryWithValues(string $code): void
    {
        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'value_collection' => json_encode([
                'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'.AbstractValue::SEPARATOR.'ecommerce'.AbstractValue::SEPARATOR.'en_US' => [
                    'data' => 'All the shoes you need!',
                    'type' => 'text',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                ],
                'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'.AbstractValue::SEPARATOR.'ecommerce'.AbstractValue::SEPARATOR.'fr_FR' => [
                    'data' => 'Les chaussures dont vous avez besoin !',
                    'type' => 'text',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
                ],
                'photo'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c' => [
                    'data' => [
                        'size' => 168107,
                        'extension' => 'jpg',
                        'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
                        'mime_type' => 'image/jpeg',
                        'original_filename' => 'shoes.jpg',
                    ],
                    'type' => 'image',
                    'channel' => null,
                    'locale' => null,
                    'attribute_code' => 'photo'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c',
                ],
            ], JSON_THROW_ON_ERROR),
            'code' => $code,
        ]);
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

    protected function givenAttributes(TemplateUuid $templateUuid): AttributeCollection
    {
        return AttributeCollection::fromArray([
            AttributeRichText::create(
                AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
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
                AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
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
                AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
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
                AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
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
                AttributeUuid::fromString('4ba33f06-de92-4366-8322-991d1bad07b9'),
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
                AttributeUuid::fromString('47c8dfb1-bf7b-4397-914e-65208dd51051'),
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
                AttributeUuid::fromString('804cddcf-bacd-43c4-8494-b3ccb51e04cc'),
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
                AttributeUuid::fromString('75ec2c1f-56ea-4db1-82c4-4efe070afccf'),
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
                AttributeUuid::fromString('b72b7414-082b-4e1e-a98f-3a04ac8193bc'),
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
                AttributeUuid::fromString('783d4957-a29b-4281-a9f5-c4621014dcfa'),
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
                AttributeUuid::fromString('b777dfe6-2518-4d0e-958d-ddb07c81b7b6'),
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
                AttributeUuid::fromString('7898eab7-c795-4989-8583-54974563e1b7'),
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
                AttributeUuid::fromString('1efc3af6-e89c-4281-9bd5-b827d9397cf7'),
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
}
