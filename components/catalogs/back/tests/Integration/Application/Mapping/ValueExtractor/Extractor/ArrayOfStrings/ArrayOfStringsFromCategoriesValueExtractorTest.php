<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromCategoriesValueExtractor;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor\ValueExtractorTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 * @covers \Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings\ArrayOfStringsFromCategoriesValueExtractor
 */
class ArrayOfStringsFromCategoriesValueExtractorTest extends ValueExtractorTestCase
{
    private ?ArrayOfStringsFromCategoriesValueExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
        $this->extractor = self::getContainer()->get(ArrayOfStringsFromCategoriesValueExtractor::class);
        $this->logAs('admin');
    }

    public function testItReturnsTheCorrectType(): void
    {
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$this->extractor->getSupportedTargetType()],
            $this->extractor,
        );
    }

    public function testItReturnsTheCategoriesLabel(): void
    {
        $this->createCategory(['code' => 'cameras', 'labels' => ['en_US' => 'Cameras']]);
        $this->createCategory(['code' => 'digital_cameras', 'labels' => ['en_US' => 'Digital cameras']]);

        $productUuid = '008cc715-77f4-4061-ab7b-8cb6d9fc4ce3';
        $this->createProduct(Uuid::fromString($productUuid), [
            new SetCategories(['cameras', 'digital_cameras']),
        ]);

        /** @var RawProduct $rawProduct */
        $rawProduct = [
            'uuid' => Uuid::fromString($productUuid),
        ];

        $result = $this->extractor->extract(
            product: $rawProduct,
            code: 'categories',
            locale: null,
            scope: null,
            parameters: [
                'label_locale' => 'en_US',
            ],
        );
        $this->assertEquals(['Cameras', 'Digital cameras'], $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        $productUuid = '008cc715-77f4-4061-ab7b-8cb6d9fc4ce3';
        $this->createProduct(Uuid::fromString($productUuid), []);

        /** @var RawProduct $rawProduct */
        $rawProduct = [
            'uuid' => Uuid::fromString($productUuid),
        ];

        $result = $this->extractor->extract(
            product: $rawProduct,
            code: 'categories',
            locale: null,
            scope: null,
            parameters: [
                'label_locale' => 'en_US',
            ],
        );

        $this->assertNull($result);
    }

    public function testItReturnsTheCategoriesCodeWhenLabelIsNotFound(): void
    {
        $this->createCategory(['code' => 'cameras', 'labels' => ['en_US' => 'Cameras']]);
        $this->createCategory(['code' => 'digital_cameras', 'labels' => ['en_US' => 'Digital cameras']]);

        $productUuid = '008cc715-77f4-4061-ab7b-8cb6d9fc4ce3';
        $this->createProduct(Uuid::fromString($productUuid), [
            new SetCategories(['cameras', 'digital_cameras']),
        ]);

        /** @var RawProduct $rawProduct */
        $rawProduct = [
            'uuid' => Uuid::fromString($productUuid),
        ];

        $result = $this->extractor->extract(
            product: $rawProduct,
            code: 'categories',
            locale: null,
            scope: null,
            parameters: [
                'label_locale' => 'fr_FR',
            ],
        );
        $this->assertEquals([
            '[cameras]',
            '[digital_cameras]',
        ], $result);
    }
}
