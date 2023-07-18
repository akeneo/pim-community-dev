<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues\CleanCategoryTemplateAttributeAndEnrichedValuesCommand;
use Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues\CleanCategoryTemplateAttributeAndEnrichedValuesCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetDeactivatedAttribute;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryTemplateAttributeAndEnrichedValuesCommandHandlerIntegration extends CategoryTestCase
{
    private GetCategoryInterface $getCategory;
    private GetDeactivatedAttribute $getDeactivatedAttribute;
    private GetAttribute $getAttribute;
    protected function setUp(): void
    {
        parent::setUp();

        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->getDeactivatedAttribute = $this->get(GetDeactivatedAttribute::class);
        $this->getAttribute = $this->get(GetAttribute::class);
    }

    public function testItCleansValueCollectionOnTemplateAttributeDeactivation(): void
    {
        $templateUuid = TemplateUuid::fromString('6344aa2a-2be9-4093-b644-259ca7aee50c');
        $categorySocks = $this->useTemplateFunctionalCatalog(
            $templateUuid->getValue(),
            'socks',
        );

        $this->updateCategoryValues((string) $categorySocks->getCode());

        $category = $this->getCategory->byCode('socks');
        $this->assertCount(3, $category->getAttributes()->getValues());
        $givenAttributes = $this->givenAttributes($templateUuid);
        $longDescriptionAttribute = $givenAttributes->getAttributeByCode('long_description');
        $this->deactivateAttribute($longDescriptionAttribute->getUuid()->getValue());

        $attributes = $this->getDeactivatedAttribute->byTemplateUuid($templateUuid);
        $deletedAttributesUuid = [];
        foreach ($attributes->getAttributes() as $attribute) {
            $attributeUuid = $attribute->getUuid();
            $deletedAttributesUuid[] = $attributeUuid;
            $command = new CleanCategoryTemplateAttributeAndEnrichedValuesCommand($templateUuid->getValue(), (string) $attributeUuid);
            $commandHandler = $this->get(CleanCategoryTemplateAttributeAndEnrichedValuesCommandHandler::class);
            ($commandHandler)($command);
        }

        $category = $this->getCategory->byCode('socks');
        $this->assertCount(2, $category->getAttributes()->getValues());
        $attributesDeletedInDatabase = $this->getAttribute->byUuids($deletedAttributesUuid);
        $this->assertCount(0, $attributesDeletedInDatabase);
    }

    private function updateCategoryValues(string $code, string $channel = 'ecommerce'): void
    {
        $query = <<<SQL
            UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
            SQL;

        $this->get('database_connection')->executeQuery($query, [
            'value_collection' => json_encode([
                'long_description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . AbstractValue::SEPARATOR . $channel . AbstractValue::SEPARATOR . 'en_US' => [
                    'data' => 'All the shoes you need!',
                    'type' => 'text',
                    'channel' => $channel,
                    'locale' => 'en_US',
                    'attribute_code' => 'long_description' . AbstractValue::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950',
                ],
                'short_description' . AbstractValue::SEPARATOR . '8dda490c-0fd1-4485-bdc5-342929783d9a' . AbstractValue::SEPARATOR . $channel . AbstractValue::SEPARATOR . 'fr_FR' => [
                    'data' => 'Chaussures !',
                    'type' => 'text',
                    'channel' => $channel,
                    'locale' => 'fr_FR',
                    'attribute_code' => 'short_description' . AbstractValue::SEPARATOR . '8dda490c-0fd1-4485-bdc5-342929783d9a',
                ],
                'photo' . AbstractValue::SEPARATOR . '8587cda6-58c8-47fa-9278-033e1d8c735c' => [
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
                    'attribute_code' => 'photo' . AbstractValue::SEPARATOR . '8587cda6-58c8-47fa-9278-033e1d8c735c',
                ],
            ], JSON_THROW_ON_ERROR),
            'code' => $code,
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
}
