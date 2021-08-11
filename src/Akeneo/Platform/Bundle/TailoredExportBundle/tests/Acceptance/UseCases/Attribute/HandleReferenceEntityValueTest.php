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
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\ReferenceEntity\ReferenceEntityLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity\InMemoryFindRecordLabels;
use PHPUnit\Framework\Assert;

final class HandleReferenceEntityValueTest extends AttributeTestCase
{
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
        $this->loadRecords();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the record code' => [
                'operations' => [],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'starck']
            ],
            'it selects the record label' => [
                'operations' => [],
                'selection' => new ReferenceEntityLabelSelection('en_US', 'designer'),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'Starck']
            ],
            'it fallbacks on the record code when the label is not found' => [
                'operations' => [],
                'selection' => new ReferenceEntityLabelSelection('en_US', 'designer'),
                'value' => new ReferenceEntityValue('record_without_label'),
                'expected' => [self::TARGET_NAME => '[record_without_label]']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ]),
                ],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ]),
                ],
                'selection' => new ReferenceEntityCodeSelection(),
                'value' => new ReferenceEntityValue('starck'),
                'expected' => [self::TARGET_NAME => 'starck']
            ],
        ];
    }

    private function loadRecords()
    {
        /** @var InMemoryFindRecordLabels $recordLabelsRepository */
        $recordLabelsRepository = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface');
        $recordLabelsRepository->addRecordLabel('designer', 'starck', 'en_US', 'Starck');
    }
}
