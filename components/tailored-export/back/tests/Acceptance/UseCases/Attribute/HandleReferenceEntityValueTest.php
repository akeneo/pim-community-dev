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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityNumberAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityOptionAttributeCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityOptionAttributeLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityTextAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordLabels;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordsAttributeValue;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindReferenceEntityOptionAttributeLabels;
use PHPUnit\Framework\Assert;

final class HandleReferenceEntityValueTest extends AttributeTestCase
{
    public function setUp(): void
    {
        $this->loadRecords();
    }

    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_reference_entity_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();
        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the record code' => [
                'operations' => [],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'starck'],
            ],
            'it selects the record label' => [
                'operations' => [],
                'selection' => new ReferenceEntityLabelSelection('en_US', 'designer'),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'Starck'],
            ],
            'it selects the record "description" text attribute' => [
                'operations' => [],
                'selection' => new ReferenceEntityTextAttributeSelection('designer', 'description', 'ecommerce', 'de_DE'),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'Bezeichnung'],
            ],
            'it selects the record "name" text attribute' => [
                'operations' => [],
                'selection' => new ReferenceEntityTextAttributeSelection('designer', 'name', null, null),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'Nom'],
            ],
            'it selects the record "size" number attribute' => [
                'operations' => [],
                'selection' => new ReferenceEntityNumberAttributeSelection('designer', 'size', ',', null, null),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => '2,67'],
            ],
            'it selects the record "tags" option attribute code' => [
                'operations' => [],
                'selection' => new ReferenceEntityOptionAttributeCodeSelection('designer', 'tags', null, null),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'large'],
            ],
            'it selects the record "tags" option attribute label' => [
                'operations' => [],
                'selection' => new ReferenceEntityOptionAttributeLabelSelection('designer', 'tags', 'en_US', null, null),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'large label'],
            ],
            'it fallbacks on the option code when option label is not found' => [
                'operations' => [],
                'selection' => new ReferenceEntityOptionAttributeLabelSelection('designer', 'tags', 'fr_FR', null, null),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => '[large]'],
            ],
            'it fallbacks on the record code when the label is not found' => [
                'operations' => [],
                'selection' => new ReferenceEntityLabelSelection('en_US', 'designer'),
                'value' => new ReferenceEntityValue('record_without_label'),
                'expected' => [self::TARGET_NAME => '[record_without_label]'],
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a'],
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    new DefaultValueOperation('n/a'),
                ],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'starck'],
            ],
            'it applies replacement operation when value is found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'stark' => 'philippe stark',
                    ]),
                ],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('stark'),
                'expected' => [self::TARGET_NAME => 'philippe stark'],
            ],
            'it does not apply replacement operation when value is not found in the mapping' => [
                'operations' => [
                    new ReplacementOperation([
                        'starck' => 'philippe stark',
                    ]),
                ],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('michel'),
                'expected' => [self::TARGET_NAME => 'michel'],
            ],
        ];
    }

    private function loadRecords(): void
    {
        /** @var InMemoryFindRecordLabels $findRecordLabels */
        $findRecordLabels = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface');
        $findRecordLabels->addRecordLabel('designer', 'starck', 'en_US', 'Starck');

        /** @var InMemoryFindRecordsAttributeValue $findRecordsAttributeValue */
        $findRecordsAttributeValue = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface');
        $findRecordsAttributeValue->addAttributeValue('designer', 'starck', 'description', 'Bezeichnung', 'ecommerce', 'de_DE');
        $findRecordsAttributeValue->addAttributeValue('designer', 'starck', 'name', 'Nom');
        $findRecordsAttributeValue->addAttributeValue('designer', 'starck', 'size', '2.67');
        $findRecordsAttributeValue->addAttributeValue('designer', 'starck', 'tags', 'large');

        /** @var InMemoryFindReferenceEntityOptionAttributeLabels $findOptionAttributeLabels */
        $findOptionAttributeLabels = self::getContainer()->get('Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface');
        $findOptionAttributeLabels->addOptionLabel('tags', 'large', 'en_US', 'large label');
    }
}
