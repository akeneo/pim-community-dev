<?php

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\Boolean;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean\BooleanFromStatusValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Boolean\BooleanFromStatusValueExtractor
 */
class BooleanFromStatusValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(BooleanFromStatusValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(BooleanFromStatusValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForStatus(): void
    {
        /** @var RawProduct $product */
        $product = [
            'is_enabled' => true,
        ];

        $result = self::getContainer()->get(BooleanFromStatusValueExtractor::class)->extract(
            product: $product,
            code: 'is_enabled',
            locale: null,
            scope: null,
            parameters: [],
        );

        $this->assertEquals(true, $result);
    }

    public function testItReturnsNullIfNullRawValue(): void
    {
        /** @var RawProduct $product */
        $product = [
            'is_enabled' => null,
        ];

        $result = self::getContainer()->get(BooleanFromStatusValueExtractor::class)->extract(
            product: $product,
            code: 'is_enabled',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: [],
        );

        $this->assertNull($result);
    }
}
