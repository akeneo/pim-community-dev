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
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SimpleSelect\SimpleSelectCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SimpleSelect\SimpleSelectLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SimpleSelectValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\AttributeOption\InMemoryFindAttributeOptionLabels;
use PHPUnit\Framework\Assert;

final class HandleSimpleSelectValueTest extends AttributeTestCase
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
        $this->loadOptions();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the code' => [
                'operations' => [],
                'selection' => new SimpleSelectCodeSelection(),
                'value' => new SimpleSelectValue('cotton'),
                'expected' => [self::TARGET_NAME => 'cotton']
            ],
            'it selects the label' => [
                'operations' => [],
                'selection' => new SimpleSelectLabelSelection('en_US', 'material'),
                'value' => new SimpleSelectValue('cotton'),
                'expected' => [self::TARGET_NAME => 'Cotton']
            ],
            'it fallbacks on the code when label is not found' => [
                'operations' => [],
                'selection' => new SimpleSelectLabelSelection('en_US', 'material'),
                'value' => new SimpleSelectValue('option_without_label'),
                'expected' => [self::TARGET_NAME => '[option_without_label]']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new SimpleSelectCodeSelection(),
                'value' => new SimpleSelectValue('cotton'),
                'expected' => [self::TARGET_NAME => 'cotton']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ])
                ],
                'selection' => new SimpleSelectCodeSelection(),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
        ];
    }

    private function loadOptions()
    {
        /** @var InMemoryFindAttributeOptionLabels $attributeOptionLabelsRepository */
        $attributeOptionLabelsRepository = self::$container->get('Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface');
        $attributeOptionLabelsRepository->addAttributeLabel('material', 'cotton', 'en_US', 'Cotton');
    }
}
