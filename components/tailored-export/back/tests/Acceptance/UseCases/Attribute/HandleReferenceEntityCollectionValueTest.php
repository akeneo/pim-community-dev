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

use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordLabels;
use PHPUnit\Framework\Assert;

final class HandleReferenceEntityCollectionValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_reference_entity_collection_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();

        $this->loadRecords();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the record codes' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionCodeSelection(','),
                'value' => new ReferenceEntityCollectionValue(['blue', 'black']),
                'expected' => [self::TARGET_NAME => 'blue,black']
            ],
            'it selects the record labels' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionLabelSelection(',', 'en_US', 'color'),
                'value' => new ReferenceEntityCollectionValue(['blue', 'reference_entity_without_label']),
                'expected' => [self::TARGET_NAME => 'Blue,[reference_entity_without_label]']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new ReferenceEntityCollectionCodeSelection(','),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new ReferenceEntityCollectionCodeSelection(','),
                'value' => new ReferenceEntityCollectionValue(['blue', 'black']),
                'expected' => [self::TARGET_NAME => 'blue,black']
            ],
            'it applies code selection only on not replaced value' => [
                'operations' => [
                    new ReplacementOperation([
                        'red' => 'Rouge de damas',
                    ]),
                ],
                'selection' => new ReferenceEntityCollectionCodeSelection('/'),
                'value' => new ReferenceEntityCollectionValue(['red', 'blue', 'black']),
                'expected' => [self::TARGET_NAME => 'Rouge de damas/blue/black'],
            ],
            'it applies label selection only on not replaced value' => [
                'operations' => [
                    new ReplacementOperation([
                        'red' => 'Rouge de damas',
                    ]),
                ],
                'selection' => new ReferenceEntityCollectionLabelSelection('/', 'en_US', 'color'),
                'value' => new ReferenceEntityCollectionValue(['red', 'blue', 'black']),
                'expected' => [self::TARGET_NAME => 'Rouge de damas/Blue/[black]'],
            ],
        ];
    }

    private function loadRecords(): void
    {
        /** @var InMemoryFindRecordLabels $recordLabelsRepository */
        $recordLabelsRepository = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface');
        $recordLabelsRepository->addRecordLabel('color', 'blue', 'en_US', 'Blue');
    }
}
