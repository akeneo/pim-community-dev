<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\ServiceApi;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\ServiceApi\ExternalApiCategory;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ExternalApiCategoryIntegration extends CategoryTestCase
{
    /** @phpstan-ignore-next-line   */
    private array $categoryDatabaseData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryDatabaseData = [
            'id' => '2',
            'code' => 'my_category',
            'parent_id' => '2',
            'parent_code' => 'my_parent_category',
            'root_id' => null,
            'updated' => '2022-12-13 14:08:10',
            'lft' => '1',
            'rgt' => '2',
            'lvl' => '0',
            'translations' => '',
            'value_collection' => '',
            'position' => '5',
        ];
    }

    public function testThrowInvalidArgumentFromWrongDatabaseArray(): void
    {
        $categoryDatabaseData = [
            'id' => '2',
            'code' => 'my_category',
        ];

        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line  */
        ExternalApiCategory::fromDatabase($categoryDatabaseData);
    }

    public function testNormalizeExternalCategoryApi(): void
    {
        $expectedNormalizedArray = [
            'code' => 'my_category',
            'parent' => 'my_parent_category',
            'updated' => '2022-12-13T14:08:10+01:00',
            'labels' => (object) [],
        ];

        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: false, withEnrichedAttributes: false);

        $this->assertIsArray($normalizedExternalApiCategory);
        $this->assertEquals($expectedNormalizedArray, $normalizedExternalApiCategory);
        $this->assertIsObject($normalizedExternalApiCategory['labels']);
    }

    public function testNormalizeExternalCategoryApiWithEmptyValues(): void
    {
        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: false, withEnrichedAttributes: true);

        $this->assertIsObject($normalizedExternalApiCategory['values']);
    }

    public function testNormalizeExternalCategoryApiWithPosition(): void
    {
        $expectedNormalizedArray = [
            'code' => 'my_category',
            'parent' => 'my_parent_category',
            'updated' => '2022-12-13T14:08:10+01:00',
            'labels' => (object) [],
            'position' => 5,
        ];

        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: true, withEnrichedAttributes: false);

        $this->assertIsArray($normalizedExternalApiCategory);
        $this->assertEquals($expectedNormalizedArray, $normalizedExternalApiCategory);
        $this->assertIsObject($normalizedExternalApiCategory['labels']);

    }

    public function testNormalizeExternalCategoryApiWithEnrichedAttributes(): void
    {
        $expectedNormalizedArray = [
            'code' => 'my_category',
            'parent' => 'my_parent_category',
            'updated' => '2022-12-13T14:08:10+01:00',
            'labels' => (object) [],
            'values' => (object) [],
        ];

        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: false, withEnrichedAttributes: true);

        $this->assertIsArray($normalizedExternalApiCategory);
        $this->assertEquals($expectedNormalizedArray, $normalizedExternalApiCategory);
        $this->assertIsObject($normalizedExternalApiCategory['labels']);
    }
}
