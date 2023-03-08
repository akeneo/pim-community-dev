<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageDataValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ImageData from ImageDataValue
 */
class CategoryDataCleanerTest extends CategoryTestCase
{
    public function testItCallsExecuteWithRightArgument(): void
    {
        $updateCategoryEnrichedValuesMock = $this->createMock(UpdateCategoryEnrichedValues::class);
        $updateCategoryEnrichedValuesMock
            ->expects(self::once())
            ->method('execute')
            ->with($this->getExpectedArgument());
        $categoryDataCleaner = new CategoryDataCleaner($updateCategoryEnrichedValuesMock);
        $categoryDataCleaner->cleanByChannelOrLocales(
            [
                'category_1' => ValueCollection::fromDatabase($this->getValuesByCodeForCategory1()),
                'category_2' => ValueCollection::fromDatabase($this->getValuesByCodeForCategory2()),
            ],
            'mobile',
            [],
        );
    }

    /**
     * @return array<string, array{
     *     data: string|ImageData|null,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }>
     *
     * @throws \JsonException
     */
    private function getValuesByCodeForCategory1(): array
    {
        return json_decode('{
            "attribute_codes": [
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            ],
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|ecommerce|fr_FR": {
                "data": "<p>Ma description enrichie pour le ecommerce</p>",
                "type": "textarea",
                "locale": "fr_FR",
                "channel": "ecommerce",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            },
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|fr_FR": {
                "data": "<p>Ma description enrichie pour le mobile</p>",
                "type": "textarea",
                "locale": "fr_FR",
                "channel": "mobile",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            },
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|en_US": {
                "data": "<p>My enriched description for mobile</p>",
                "type": "textarea",
                "locale": "en_US",
                "channel": "mobile",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            }
        }', true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, array{
     *     data: string|ImageData|null,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }>
     *
     * @throws \JsonException
     */
    private function getValuesByCodeForCategory2(): array
    {
        return json_decode('{
            "attribute_codes": [
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            ],
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|print|fr_FR": {
                "data": "<p>Ma description enrichie pour l\'imprimerie</p>",
                "type": "textarea",
                "locale": "fr_FR",
                "channel": "ecommerce",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            }
        }', true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, ValueCollection>
     *
     * @throws \JsonException
     */
    private function getExpectedArgument(): array
    {
        return [
            'category_1' => ValueCollection::fromDatabase([
                'attribute_codes' => [
                    'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233',
                ],
                'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|ecommerce|fr_FR' => [
                    'data' => '<p>Ma description enrichie pour le ecommerce</p>',
                    'type' => 'textarea',
                    'locale' => 'fr_FR',
                    'channel' => 'ecommerce',
                    'attribute_code' => 'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233',
                ],
            ]),
        ];
    }
}
