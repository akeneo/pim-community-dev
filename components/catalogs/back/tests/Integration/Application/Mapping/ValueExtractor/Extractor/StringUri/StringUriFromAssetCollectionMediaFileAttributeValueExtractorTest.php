<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\StringUri;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri\StringUriFromAssetCollectionMediaFileAttributeValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri\StringUriFromAssetCollectionMediaFileAttributeValueExtractor
 */
class StringUriFromAssetCollectionMediaFileAttributeValueExtractorTest extends ValueExtractorTestCase
{
    private ?StringUriFromAssetCollectionMediaFileAttributeValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->extractor = self::getContainer()->get(StringUriFromAssetCollectionMediaFileAttributeValueExtractor::class);
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$this->extractor->getSupportedTargetType()],
            $this->extractor,
        );
    }

    /**
     * @group ce
     */
    public function testItReturnsNull(): void
    {
        /** @var RawProduct $product */
        $product = [
            'raw_values' => [
                'images' => [
                    'ecommerce' => [
                        'en_US' => [
                            'absorb_atmosphere_1',
                            'admete_atmosphere_2',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->extractor->extract(
            product: $product,
            code: 'images',
            locale: 'en_US',
            scope: 'ecommerce',
            parameters: ['label_locale' => 'en_US'],
        );

        $this->assertNull($result);
    }
}
