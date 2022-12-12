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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionNumberAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionOptionAttributeCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionOptionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionOptionCollectionAttributeCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionOptionCollectionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionTextAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordLabels;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordsAttributeValue;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindReferenceEntityOptionAttributeLabels;
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
            'it selects the records "description" text attribute' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionTextAttributeSelection(';', 'color', 'description', 'ecommerce', 'de_DE'),
                'value' => new ReferenceEntityCollectionValue(['blue', 'red', 'green']),
                'expected' => [self::TARGET_NAME => 'Blau;;Grun'],
            ],
            'it selects the records "name" text attribute' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionTextAttributeSelection(';', 'color', 'name', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'blue', 'green']),
                'expected' => [self::TARGET_NAME => 'Red name;Blue name;Green name'],
            ],
            'it selects the records "size" text attribute' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionNumberAttributeSelection('|', 'color', 'size', ',', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'blue', 'green']),
                'expected' => [self::TARGET_NAME => '1000000|5,6|'],
            ],
            'it selects the records "tags" option attribute code' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionOptionAttributeCodeSelection(',', 'color', 'tags', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'green', 'record_with_no_value', 'blue']),
                'expected' => [self::TARGET_NAME => 'large,medium,,small'],
            ],
            'it selects the records "tags" option attribute label' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionOptionAttributeLabelSelection(';', 'color', 'tags', 'en_US', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'record_with_no_value', 'blue', 'green']),
                'expected' => [self::TARGET_NAME => 'large label;;small label;medium label'],
            ],
            'it fallbacks on the option code when option label is not found' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionOptionAttributeLabelSelection('|', 'color', 'tags', 'fr_FR', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'blue', 'record_with_no_value', 'green']),
                'expected' => [self::TARGET_NAME => 'gros label|[small]||moyen label'],
            ],
            'it selects the records "collection" option collection attribute codes' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionOptionCollectionAttributeCodeSelection(',', 'color', 'collection', ';', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'green', 'record_with_no_value', 'blue']),
                'expected' => [self::TARGET_NAME => 'autumn,winter;spring,,spring;summer'],
            ],
            'it selects the records "collection" option collection attribute labels (with fallback on option codes)' => [
                'operations' => [],
                'selection' => new ReferenceEntityCollectionOptionCollectionAttributeLabelSelection(';', 'color', 'collection', '|', 'fr_FR', null, null),
                'value' => new ReferenceEntityCollectionValue(['red', 'green', 'record_with_no_value', 'blue']),
                'expected' => [self::TARGET_NAME => 'Automne;Hiver|Printemps;;Printemps|[summer]'],
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
        /** @var InMemoryFindRecordLabels $findRecordLabels */
        $findRecordLabels = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface');
        $findRecordLabels->addRecordLabel('color', 'blue', 'en_US', 'Blue');

        /** @var InMemoryFindRecordsAttributeValue $findRecordsAttributeValue */
        $findRecordsAttributeValue = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface');
        $findRecordsAttributeValue->addAttributeValue('color', 'blue', 'description', 'Blau', 'ecommerce', 'de_DE');
        $findRecordsAttributeValue->addAttributeValue('color', 'blue', 'name', 'Blue name');
        $findRecordsAttributeValue->addAttributeValue('color', 'blue', 'size', '5.6');
        $findRecordsAttributeValue->addAttributeValue('color', 'blue', 'tags', 'small');
        $findRecordsAttributeValue->addAttributeValue('color', 'blue', 'collection', ['spring', 'summer']);

        $findRecordsAttributeValue->addAttributeValue('color', 'red', 'description', 'Rot', 'ecommerce', 'fr_FR');
        $findRecordsAttributeValue->addAttributeValue('color', 'red', 'name', 'Red name');
        $findRecordsAttributeValue->addAttributeValue('color', 'red', 'size', '1000000');
        $findRecordsAttributeValue->addAttributeValue('color', 'red', 'tags', 'large');
        $findRecordsAttributeValue->addAttributeValue('color', 'red', 'collection', ['autumn']);

        $findRecordsAttributeValue->addAttributeValue('color', 'green', 'description', 'Grun', 'ecommerce', 'de_DE');
        $findRecordsAttributeValue->addAttributeValue('color', 'green', 'name', 'Green name');
        $findRecordsAttributeValue->addAttributeValue('color', 'green', 'tags', 'medium');
        $findRecordsAttributeValue->addAttributeValue('color', 'green', 'collection', ['winter', 'spring']);

        /** @var InMemoryFindReferenceEntityOptionAttributeLabels $findOptionAttributeLabels */
        $findOptionAttributeLabels = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface');
        $findOptionAttributeLabels->addOptionLabel('tags', 'large', 'en_US', 'large label');
        $findOptionAttributeLabels->addOptionLabel('tags', 'large', 'fr_FR', 'gros label');
        $findOptionAttributeLabels->addOptionLabel('tags', 'medium', 'en_US', 'medium label');
        $findOptionAttributeLabels->addOptionLabel('tags', 'medium', 'fr_FR', 'moyen label');
        $findOptionAttributeLabels->addOptionLabel('tags', 'small', 'en_US', 'small label');
        $findOptionAttributeLabels->addOptionLabel('collection', 'spring', 'en_US', 'Spring');
        $findOptionAttributeLabels->addOptionLabel('collection', 'spring', 'fr_FR', 'Printemps');
        $findOptionAttributeLabels->addOptionLabel('collection', 'summer', 'en_US', 'Summer');
        $findOptionAttributeLabels->addOptionLabel('collection', 'autumn', 'fr_FR', 'Automne');
        $findOptionAttributeLabels->addOptionLabel('collection', 'winter', 'fr_FR', 'Hiver');
    }
}
