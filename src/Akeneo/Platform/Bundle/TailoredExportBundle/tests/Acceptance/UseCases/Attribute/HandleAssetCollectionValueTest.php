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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Attribute;

use Akeneo\Platform\TailoredExport\Domain\Model\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Asset\InMemoryFindAssetLabels;
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
        $productMapper = $this->getProductMapper();
        $this->loadAssetLabels();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $productMapper->map($columnCollection, $valueCollection);

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
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ]),
                ],
                'selection' => new AssetCollectionCodeSelection(',', 'packshot', 'my_asset_collection'),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ]),
                ],
                'selection' => new AssetCollectionCodeSelection(',', 'packshot', 'my_asset_collection'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1'], 'my_desk', 'ecommerce', null),
                'expected' => [self::TARGET_NAME => 'packshot_0,packshot_1']
            ],
        ];
    }

    private function loadAssetLabels()
    {
        /** @var InMemoryFindAssetLabels $assetLabelsRepository */
        $assetLabelsRepository = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindAssetLabelsInterface');
        $assetLabelsRepository->addAssetLabel('packshot', 'packshot_0', 'en_US', 'Packshot 0');
    }
}
