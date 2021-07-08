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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Asset\InMemoryFindAssetLabelTranslation;
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
            [
                'operations' => [],
                'selection' => new AssetCollectionCodeSelection(','),
                'value' => new AssetCollectionValue([]),
                'expected' => [self::TARGET_NAME => '']
            ],
            [
                'operations' => [],
                'selection' => new AssetCollectionCodeSelection(','),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1']),
                'expected' => [self::TARGET_NAME => 'packshot_0,packshot_1']
            ],
            [
                'operations' => [],
                'selection' => new AssetCollectionLabelSelection('|', 'en_US', 'packshot'),
                'value' => new AssetCollectionValue(['packshot_0', 'packshot_1']),
                'expected' => [self::TARGET_NAME => 'Packshot 0|[packshot_1]']
            ]
        ];
    }

    private function loadAssetLabels()
    {
        /** @var InMemoryFindAssetLabelTranslation $assetLabelsRepository */
        $assetLabelsRepository = self::$container->get('akeneo_assetmanager.infrastructure.persistence.query.enrich.find_asset_label_translation_public_api');
        $assetLabelsRepository->addAssetLabel('packshot', 'packshot_0', 'en_US', 'Packshot 0');
    }
}
