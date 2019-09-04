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

    public static function sameButOrderNotGuaranteed(array $expectedRows, array $rows): void
    {
        Assert::assertCount(count($expectedRows), $rows);
        foreach ($expectedRows as $expectedRow) {
            $identifier = $expectedRow->identifier();
            $actualRow = null;

            foreach ($rows as $row) {
                if ($row->identifier() === $identifier) {
                    $actualRow = $row;
                    break;
                }
            }

            Assert::assertNotNull($actualRow);
            self::assertSameRow($expectedRow, $actualRow);
        }
    }

    private static function assertSameRow(Row $expectedRow, Row $row): void
    {
        $expectedGroups = $expectedRow->groupCodes();
        $groups = $row->groupCodes();

        Assert::assertSame($expectedRow->identifier(), $row->identifier());
        Assert::assertSame($expectedRow->parentCode(), $row->parentCode());
        Assert::assertSame($expectedRow->completeness(), $row->completeness());
        Assert::assertSame($expectedRow->childrenCompleteness(), $row->childrenCompleteness());
        Assert::assertSame($expectedRow->checked(), $row->checked());
        Assert::assertSame(sort($expectedGroups), sort($groups));
        Assert::assertSame($expectedRow->familyCode(), $row->familyCode());
        Assert::assertSame($expectedRow->technicalId(), $row->technicalId());
        Assert::assertSame($expectedRow->searchId(), $row->searchId());
        Assert::assertSame($expectedRow->documentType(), $row->documentType());
        Assert::assertNotNull($row->updated());
        Assert::assertNotNull($row->created());

        null !== $expectedRow->image() ?
            Assert::assertTrue($expectedRow->image()->getData()->getHash() === $row->image()->getData()->getHash()):
            Assert::assertNull($row->image());

        Assert::assertSame($expectedRow->label(), $row->label());

        Assert::assertSame($expectedRow->values()->count(), $row->values()->count());
        foreach ($expectedRow->values() as $value) {
            Assert::assertNotNull($row->values()->getSame($value));
        }
    }
}
