<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use PHPUnit\Framework\Assert;

final class AssertRows
{
    public static function same(array $expectedRows, array $rows): void
    {
        Assert::assertCount(count($expectedRows), $rows);
        foreach ($expectedRows as $index => $expectedRow) {
            self::assertSameRow($expectedRow, $rows[$index]);
        }
    }

    private static function assertSameRow(Row $expectedRow, Row $row): void
    {
        $expectedGroups = $expectedRow->groups();
        $groups = $row->groups();

        Assert::assertSame($expectedRow->identifier(), $row->identifier());
        Assert::assertSame($expectedRow->parent(), $row->parent());
        Assert::assertSame($expectedRow->completeness(), $row->completeness());
        Assert::assertSame($expectedRow->childrenCompleteness(), $row->childrenCompleteness());
        Assert::assertSame($expectedRow->checked(), $row->checked());
        Assert::assertSame(sort($expectedGroups), sort($groups));
        Assert::assertSame($expectedRow->family(), $row->family());
        Assert::assertSame($expectedRow->technicalId(), $row->technicalId());
        Assert::assertSame($expectedRow->searchId(), $row->searchId());
        Assert::assertSame($expectedRow->documentType(), $row->documentType());
        Assert::assertNotNull($row->updated());
        Assert::assertNotNull($row->created());

        null !== $expectedRow->image() ?
            Assert::assertTrue($expectedRow->image()->isEqual($row->image())):
            Assert::assertNull($row->image());

        Assert::assertSame($expectedRow->label(), $row->label());

        Assert::assertSame($expectedRow->values()->count(), $row->values()->count());
        foreach ($expectedRow->values() as $value) {
            Assert::assertNotNull($row->values()->getSame($value));
        }
    }
}
