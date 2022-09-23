<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Test\Acceptance\UseCases\Attribute;

use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\AssetCollection\AssetCollectionMediaLinkSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\MediaLinkAsMainMedia;
use Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Asset\InMemoryFindAssetLabels;
use Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Asset\InMemoryFindAssetMainMediaAttribute;
use Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Asset\InMemoryFindAssetMainMediaData;
use PHPUnit\Framework\Assert;

final class HandleAssetCollectionValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_an_asset_collection_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();
        $this->loadAssetLabels();
        $this->loadAssetMainMediaData();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it returns an empty string if the asset collection is empty' => [
                'operations' => [],
                'selection' => new AssetCollectionCodeSelection(',', 'packshot', 'my_asset_collection'),
                'value' => new AssetCollectionValue([], 'my_desk', null, 'en_US'),
                'expected' => [self::TARGET_NAME => '']
            ],
            'it selects the asset codes' => [
                'operations' => [],
                'selection' => new AssetCollectionCodeSelection(',', 'packshot', 'my_asset_collection'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'packshot_0,packshot_1']
            ],
            'it selects the asset labels' => [
                'operations' => [],
                'selection' => new AssetCollectionLabelSelection('|', 'en_US', 'packshot', 'my_asset_collection'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'Packshot 0|[packshot_1]']
            ],
            'it selects the file key of asset main media file' => [
                'operations' => [],
                'selection' => new AssetCollectionMediaFileSelection(';', 'ecommerce', 'en_US', 'packshot', 'my_asset_collection', 'file_key'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'test/packshot_0.jpg']
            ],
            'it selects the file path of asset main media file' => [
                'operations' => [],
                'selection' => new AssetCollectionMediaFileSelection(';', 'ecommerce', 'en_US', 'packshot', 'my_asset_collection', 'file_path'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'files/my_desk/my_asset_collection/ecommerce/packshot_0.jpg']
            ],
            'it selects the original filename of asset main media file' => [
                'operations' => [],
                'selection' => new AssetCollectionMediaFileSelection(';', 'ecommerce', 'en_US', 'packshot', 'my_asset_collection', 'original_filename'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'packshot_0.jpg']
            ],
            'it selects the data of asset main media link' => [
                'operations' => [],
                'selection' => new AssetCollectionMediaLinkSelection(',', 'ecommerce', 'en_US', 'notice', 'my_asset_collection', false),
                'value' => new AssetCollectionValue(['notice_0', 'notice_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'http://packshot_0.com']
            ],
            'it selects the data of asset main media link with prefix and suffix' => [
                'operations' => [],
                'selection' => new AssetCollectionMediaLinkSelection(',', 'ecommerce', 'en_US', 'asset_collection_with_prefix_and_suffix', 'my_asset_collection', true),
                'value' => new AssetCollectionValue(['asset0'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'https://test.fr/asset0.png']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new AssetCollectionCodeSelection(',', 'packshot', 'my_asset_collection'),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new AssetCollectionCodeSelection(',', 'packshot', 'my_asset_collection'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'packshot_0,packshot_1']
            ],
        ];
    }

    private function loadAssetLabels(): void
    {
        /** @var InMemoryFindAssetLabels $assetLabelsRepository */
        $assetLabelsRepository = self::$container->get('Akeneo\Platform\Syndication\Domain\Query\FindAssetLabelsInterface');
        $assetLabelsRepository->addAssetLabel('packshot', 'packshot_0', 'en_US', 'Packshot 0');
    }

    private function loadAssetMainMediaData(): void
    {
        /** @var InMemoryFindAssetMainMediaData $assetMainMediaDataRepository */
        $assetMainMediaDataRepository = self::$container->get('Akeneo\Platform\Syndication\Domain\Query\FindAssetMainMediaDataInterface');
        $assetMainMediaDataRepository->addAssetMainMediaData(
            'packshot',
            'packshot_0',
            'ecommerce',
            'en_US',
            [
                'fileKey' => 'test/packshot_0.jpg',
                'filePath' => 'test/packshot_0.jpg',
                'originalFilename' => 'packshot_0.jpg'
            ]
        );
        $assetMainMediaDataRepository->addAssetMainMediaData(
            'notice',
            'notice_0',
            'ecommerce',
            'en_US',
            'http://packshot_0.com'
        );

        $assetMainMediaDataRepository->addAssetMainMediaData(
            'asset_collection_with_prefix_and_suffix',
            'asset0',
            'ecommerce',
            'en_US',
            'asset0'
        );

        /** @var InMemoryFindAssetMainMediaAttribute $assetMainMediaAttribute */
        $assetMainMediaAttribute = self::$container->get('Akeneo\Platform\Syndication\Domain\Query\AssetCollection\FindAssetMainMediaAttributeInterface');
        $assetMainMediaAttribute->addAttributeAsMainMedia(
            'asset_collection_with_prefix_and_suffix',
            new MediaLinkAsMainMedia(
                true,
                true,
                'https://test.fr/',
                '.png'
            )
        );
    }
}
