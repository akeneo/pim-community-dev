<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\ServiceApi;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\ServiceApi\InternalApiCategory;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InternalApiCategoryIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testNormalizeInternalCategoryApi(): void
    {
        $categoryParent = $this->createOrUpdateCategory(
            code: 'my_category_parent',
            labels: [
                'en_US' => 'english',
                'de_DE' => 'deutch',
                'fr_FR' => 'French',
            ]
        );

        $category = $this->createOrUpdateCategory(
            code: 'my_category',
            labels: [
                'en_US' => 'english',
                'de_DE' => 'deutch',
                'fr_FR' => 'French',
            ],
            parentId: $categoryParent->getId()->getValue(),
        );
        $expectedNormalizedArray = [
            'id' => $category->getId()->getValue(),
            'parent' => $categoryParent->getId()->getValue(),
            'root_id' => null,
            'template_uuid' => null,
            'properties' => [
                'code' => "my_category",
                'labels' => [
                    'en_US' => 'english',
                    'de_DE' => 'deutch',
                    'fr_FR' => 'French',
                ],
            ],
            'attributes' => null,
            'permissions' => null,
            'isRoot' => false,
            'root' => [
                'id' => $categoryParent->getId()->getValue(),
                'parent' => null,
                'root_id' => null,
                'template_uuid' => null,
                'properties' => [
                    'code' => "my_category_parent",
                    'labels' => [
                        'en_US' => 'english',
                        'de_DE' => 'deutch',
                        'fr_FR' => 'French',
                    ],
                ],
                'attributes' => null,
                'permissions' => null,
                'isRoot' => true,
                'root' => null,
            ],
        ];

        $internalApiCategory = InternalApiCategory::fromCategory($category, $categoryParent);
        $normalizedInternalApiCategory = $internalApiCategory->normalize();
        $this->assertIsArray($normalizedInternalApiCategory);
        $this->assertEquals($expectedNormalizedArray, $normalizedInternalApiCategory);
    }

    public function testNormalizeExternalCategoryApiWithouthRoot(): void
    {
        $category = $this->createOrUpdateCategory(
            code: 'my_category',
            labels: [
                'en_US' => 'english',
                'de_DE' => 'deutch',
                'fr_FR' => 'French',
            ],
        );
        $expectedNormalizedArray = [
            'id' => $category->getId()->getValue(),
            'parent' => null,
            'root_id' => null,
            'template_uuid' => null,
            'properties' => [
                'code' => "my_category",
                'labels' => [
                    'en_US' => 'english',
                    'de_DE' => 'deutch',
                    'fr_FR' => 'French',
                ],
            ],
            'attributes' => null,
            'permissions' => null,
            'isRoot' => true,
            'root' => null
        ];

        $internalApiCategory = InternalApiCategory::fromCategory($category, null);
        $normalizedInternalApiCategory = $internalApiCategory->normalize();

        $this->assertIsArray($normalizedInternalApiCategory);
        $this->assertEquals($expectedNormalizedArray, $normalizedInternalApiCategory);
    }

    public function testNormalizeExternalCategoryApiWithEmptyLabels(): void
    {
        $categoryParent = $this->createOrUpdateCategory(
            code: 'my_category_parent',
        );

        $category = $this->createOrUpdateCategory(
            code: 'my_category',
            parentId: $categoryParent->getId()->getValue(),
        );
        $expectedNormalizedArray = [
            'id' => $category->getId()->getValue(),
            'parent' => $categoryParent->getId()->getValue(),
            'root_id' => null,
            'template_uuid' => null,
            'properties' => [
                'code' => "my_category",
                'labels' => (object) [],
            ],
            'attributes' => null,
            'permissions' => null,
            'isRoot' => false,
            'root' => [
                'id' => $categoryParent->getId()->getValue(),
                'parent' => null,
                'root_id' => null,
                'template_uuid' => null,
                'properties' => [
                    'code' => "my_category_parent",
                    'labels' => (object) [],
                ],
                'attributes' => null,
                'permissions' => null,
                'isRoot' => true,
                'root' => null,
            ],
        ];

        $internalApiCategory = InternalApiCategory::fromCategory($category, $categoryParent);
        $normalizedInternalApiCategory = $internalApiCategory->normalize();
        $this->assertIsArray($normalizedInternalApiCategory);
        $this->assertEquals($expectedNormalizedArray, $normalizedInternalApiCategory);
        $this->assertIsObject($normalizedInternalApiCategory['properties']['labels']);
        $this->assertIsObject($normalizedInternalApiCategory['root']['properties']['labels']);
    }
}
