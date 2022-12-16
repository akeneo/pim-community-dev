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
        ];
    }

    public function testCreateExternalCategoryApiFromDatabase(): void
    {
        $externalCategoryApi = ExternalApiCategory::fromDatabase($this->categoryDatabaseData);

        $this->assertInstanceOf(ExternalApiCategory::class, $externalCategoryApi);
        $this->assertEquals('my_category', $externalCategoryApi->getCode());
        $this->assertEquals(2, $externalCategoryApi->getParentId());
        $this->assertEquals('my_parent_category', $externalCategoryApi->getParentCode());
        $this->assertEquals('2022-12-13T14:08:10+01:00', $externalCategoryApi->getUpdated());
        $this->assertEquals([], $externalCategoryApi->getLabels());
        $this->assertNull($externalCategoryApi->getValues());
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
            'labels' => [],
        ];

        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: false, withEnrichedAttributes: false);

        $this->assertIsArray($normalizedExternalApiCategory);
        $this->assertSame($expectedNormalizedArray, $normalizedExternalApiCategory);
    }

    public function testNormalizeExternalCategoryApiWithPosition(): void
    {
        $expectedNormalizedArray = [
            'code' => 'my_category',
            'parent' => 'my_parent_category',
            'updated' => '2022-12-13T14:08:10+01:00',
            'labels' => [],
            'position' => null,
        ];

        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: true, withEnrichedAttributes: false);

        $this->assertIsArray($normalizedExternalApiCategory);
        $this->assertSame($expectedNormalizedArray, $normalizedExternalApiCategory);

    }

    public function testNormalizeExternalCategoryApiWithEnrichedAttributes(): void
    {
        $expectedNormalizedArray = [
            'code' => 'my_category',
            'parent' => 'my_parent_category',
            'updated' => '2022-12-13T14:08:10+01:00',
            'labels' => [],
            'values' => null,
        ];

        $normalizedExternalApiCategory = ExternalApiCategory::fromDatabase($this->categoryDatabaseData)
            ->normalize(withPosition: false, withEnrichedAttributes: true);

        $this->assertIsArray($normalizedExternalApiCategory);
        $this->assertSame($expectedNormalizedArray, $normalizedExternalApiCategory);
    }
}
