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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntity\ReferenceEntityTextAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordLabels;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordsAttributeValue;
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
    }
}
