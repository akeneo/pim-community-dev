<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Application\Command\CleanCategoryEnrichedValuesByTemplateUuid;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues\CleanCategoryTemplateAndEnrichedValuesCommand;
use Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues\CleanCategoryTemplateAndEnrichedValuesCommandHandler;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryTemplateAndEnrichedValuesCommandHandlerIntegration extends CategoryTestCase
{
    public function testItCleansValueCollectionOnTemplateDeactivation(): void
    {
        $categorySocks = $this->useTemplateFunctionalCatalog(
            '6344aa2a-2be9-4093-b644-259ca7aee50c',
            'socks',
        );

        $this->updateCategoryValues((string) $categorySocks->getCode());

        $getCategory = $this->get(GetCategoryInterface::class);
        $category = $getCategory->byCode('socks');
        $this->assertCount(3, $category->getAttributes()->getValues());

        $command = new CleanCategoryTemplateAndEnrichedValuesCommand('6344aa2a-2be9-4093-b644-259ca7aee50c');
        $commandHandler = $this->get(CleanCategoryTemplateAndEnrichedValuesCommandHandler::class);
        ($commandHandler)($command);

        $category = $getCategory->byCode('socks');
        $this->assertCount(1, $category->getAttributes()->getValues());
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
}
