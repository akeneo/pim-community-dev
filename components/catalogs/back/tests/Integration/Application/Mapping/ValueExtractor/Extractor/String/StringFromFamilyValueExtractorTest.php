<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromFamilyValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String\StringFromFamilyValueExtractor
 */
class StringFromFamilyValueExtractorTest extends ValueExtractorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purgeDataAndLoadMinimalCatalog();
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
        $this->logAs('admin');
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[self::getContainer()->get(StringFromFamilyValueExtractor::class)->getSupportedTargetType()],
            self::getContainer()->get(StringFromFamilyValueExtractor::class),
        );
    }

    public function testItReturnsTheValueForFamily(): void
    {
        $this->createFamily(['code' => 'shoes', 'labels' => [
            'fr_FR' => 'Chaussures',
            'en_US' => 'Shoes',
        ]]);

        $productUuid = '008cc715-77f4-4061-ab7b-8cb6d9fc4ce3';
        $this->createProduct(Uuid::fromString($productUuid), [
            new SetFamily('shoes'),
        ]);

        /** @var RawProduct $product */
        $product = [
            'family_code' => 'shoes',
        ];

        $result = self::getContainer()->get(StringFromFamilyValueExtractor::class)->extract(
            product: $product,
            code: 'family',
            locale: null,
            scope: null,
            parameters: [
                'label_locale' => 'fr_FR',
            ],
        );

        $this->assertEquals('Chaussures', $result);
    }

    public function testItReturnsTheFamilyCodeAsLabel(): void
    {
        $this->createFamily(['code' => 'shoes', 'labels' => [
            'fr_FR' => 'Chaussures',
        ]]);

        $productUuid = '008cc715-77f4-4061-ab7b-8cb6d9fc4ce3';
        $this->createProduct(Uuid::fromString($productUuid), [
            new SetFamily('shoes'),
        ]);

        /** @var RawProduct $product */
        $product = [
            'family_code' => 'shoes',
        ];

        $result = self::getContainer()->get(StringFromFamilyValueExtractor::class)->extract(
            product: $product,
            code: 'family',
            locale: null,
            scope: null,
            parameters: [
                'label_locale' => 'en_US',
            ],
        );

        $this->assertEquals('[shoes]', $result);
    }
}
