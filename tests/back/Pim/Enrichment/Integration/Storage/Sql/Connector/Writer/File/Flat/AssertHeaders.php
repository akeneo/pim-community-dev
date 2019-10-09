<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat;

use PHPUnit\Framework\Assert;

final class AssertHeaders
{
    public static function same(array $expectedHeaders, array $actualHeaders): void
    {
        Assert::assertCount(count($expectedHeaders), $actualHeaders);

        $expectedHeaderStrings = [];
        foreach ($expectedHeaders as $expectedHeader) {
            $expectedHeaderStrings = array_merge($expectedHeaderStrings, $expectedHeader->generateHeaderStrings());
        }

        $actualHeaderStrings = [];
        foreach ($actualHeaders as $actualHeader) {
            $actualHeaderStrings = array_merge($actualHeaderStrings, $actualHeader->generateHeaderStrings());
        }

        Assert::assertCount(count($expectedHeaderStrings), $actualHeaderStrings);

        foreach ($expectedHeaderStrings as $expectedHeaderString) {
            Assert::assertTrue(
                in_array($expectedHeaderString, $actualHeaderStrings),
                sprintf('Unable to find %s in actual header strings', $expectedHeaderString)
            );
        }
    }
}
