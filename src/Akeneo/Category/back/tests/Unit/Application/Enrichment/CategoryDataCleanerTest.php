<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                'category_1' => $this->getValuesByCodeForCategory1(),
                'category_2' => $this->getValuesByCodeForCategory2(),
            ],
            'mobile',
            [],
        );
    }

    private function getValuesByCodeForCategory1(): string
    {
        return '{
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
        }';
    }

    private function getValuesByCodeForCategory2(): string
    {
        return '{
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
        }';
    }

    /**
     * @return array<string, string>
     *
     * @throws \JsonException
     */
    private function getExpectedArgument(): array
    {
        return [
            'category_1' => json_encode([
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
            ], JSON_THROW_ON_ERROR),
        ];
    }
}
