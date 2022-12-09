<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\PerformanceAnalytics\Integration\Query;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Infrastructure\AntiCorruptionLayer\ACLGetCategoryCodesWithAncestors;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\PerformanceAnalytics\Integration\PerformanceAnalyticsTestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ACLGetCategoryCodesWithAncestorsIntegration extends PerformanceAnalyticsTestCase
{
    private ACLGetCategoryCodesWithAncestors $aclGetCategoryCodesWithAncestors;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aclGetCategoryCodesWithAncestors = $this->get(ACLGetCategoryCodesWithAncestors::class);

        $this->createCategory(['code' => 'A']);
        $this->createCategory(['code' => 'A1', 'parent' => 'A']);
        $this->createCategory(['code' => 'A11', 'parent' => 'A1']);
        $this->createCategory(['code' => 'A12', 'parent' => 'A1']);
        $this->createCategory(['code' => 'A2', 'parent' => 'A']);
        $this->createCategory(['code' => 'A21', 'parent' => 'A2']);
        $this->createCategory(['code' => 'B']);
        $this->createCategory(['code' => 'B1', 'parent' => 'B']);
        $this->createCategory(['code' => 'B11', 'parent' => 'B1']);
        $this->createCategory(['code' => 'C']);
        $this->createCategory(['code' => 'C1', 'parent' => 'C']);
    }

    public function testItReturnsCategoryCodesWithAncestors(): void
    {
        $uuid1 = $this->createProduct('identifier1', [new SetCategories(['A11', 'B1'])])->getUuid();
        $uuid2 = $this->createProduct('identifier2', [new SetCategories(['A21', 'A1'])])->getUuid();
        $uuid3 = $this->createProduct('identifier3', [])->getUuid();
        $unknownUuid = Uuid::uuid4();

        self::assertCount(0, $this->aclGetCategoryCodesWithAncestors->forProductUuids([]));

        $results = $this->aclGetCategoryCodesWithAncestors->forProductUuids([$uuid1, $uuid2, $uuid3, $unknownUuid]);
        self::assertArrayHasKey($uuid1->toString(), $results);
        self::assertArrayHasKey($uuid2->toString(), $results);
        self::assertArrayHasKey($uuid3->toString(), $results);
        self::assertArrayNotHasKey($unknownUuid->toString(), $results);
        self::assertCount(3, $results);

        self::assertSame(['A', 'A1', 'A11', 'B', 'B1'], $this->getOrderedStringCategoryCodesForProduct($results, $uuid1));
        self::assertSame(['A', 'A1', 'A2', 'A21'], $this->getOrderedStringCategoryCodesForProduct($results, $uuid2));
        self::assertSame([], $results[$uuid3->toString()]);
    }

    public function testItReturnsCategoryCodesWithAncestorsForVariants(): void
    {
        $this->createAttribute('name');
        $this->createAttribute('sub_name');
        $this->createAttribute('size', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('size', ['S', 'M', 'L', 'XL']);
        $this->createAttribute('color', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('color', ['red', 'blue', 'white', 'black']);
        $this->createFamily('clothes', ['attributes' => ['name', 'sub_name', 'size', 'color']]);
        $this->createFamilyVariant('size_variant_clothes', 'clothes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => [],
                ],
            ],
        ]);
        $this->createProductModel('root', null, 'size_variant_clothes', ['B11']);
        $this->createProductModel('sub', 'root', 'size_variant_clothes', ['A21']);

        $uuid1 = $this->createProduct('identifier1', [
            new SetFamily('clothes'),
            new ChangeParent('sub'),
            new SetCategories(['C1']),
            new SetSimpleSelectValue('size', null, null, 'S'),
        ])->getUuid();
        $uuid2 = $this->createProduct('identifier2', [
            new SetFamily('clothes'),
            new ChangeParent('sub'),
            new SetSimpleSelectValue('size', null, null, 'M'),
        ])->getUuid();

        self::assertCount(0, $this->aclGetCategoryCodesWithAncestors->forProductUuids([]));

        $results = $this->aclGetCategoryCodesWithAncestors->forProductUuids([$uuid1, $uuid2]);
        self::assertArrayHasKey($uuid1->toString(), $results);
        self::assertArrayHasKey($uuid2->toString(), $results);
        self::assertCount(2, $results);

        self::assertSame(['A', 'A2', 'A21', 'B', 'B1', 'B11', 'C', 'C1'], $this->getOrderedStringCategoryCodesForProduct($results, $uuid1));
        self::assertSame(['A', 'A2', 'A21', 'B', 'B1', 'B11'], $this->getOrderedStringCategoryCodesForProduct($results, $uuid2));
    }

    /**
     * @param array<string, CategoryCode[]> $results
     * @return string[]
     */
    private function getOrderedStringCategoryCodesForProduct(array $results, UuidInterface $productUuid): array
    {
        $stringCategoryCodes = \array_map(
            static fn (CategoryCode $categoryCode): string => $categoryCode->toString(),
            $results[$productUuid->toString()]
        );
        \sort($stringCategoryCodes);

        return $stringCategoryCodes;
    }
}
