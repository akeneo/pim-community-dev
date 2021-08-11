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
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FileKeySelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FileNameSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FilePathSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use PHPUnit\Framework\Assert;

final class HandleFileValueTest extends AttributeTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_can_transform_a_file_value(
        array $operations,
        SelectionInterface $selection,
        SourceValueInterface $value,
        array $expected
    ): void {
        $mapValuesQueryHandler = $this->getMapValuesQueryHandler();

        $columnCollection = $this->createSingleSourceColumnCollection($operations, $selection);
        $valueCollection = $this->createSingleValueValueCollection($value);

        $mappedProduct = $mapValuesQueryHandler->handle($columnCollection, $valueCollection);

        Assert::assertSame($expected, $mappedProduct);
    }

    public function provider(): array
    {
        return [
            'it selects the file name' => [
                'operations' => [],
                'selection' => new FileNameSelection(self::ATTRIBUTE_CODE),
                'value' => new FileValue('my_product', 'catalog', 'file_key_hash', 'my_file.jpg', null, null),
                'expected' => [self::TARGET_NAME => 'my_file.jpg']
            ],
            'it selects the file key' => [
                'operations' => [],
                'selection' => new FileKeySelection(self::ATTRIBUTE_CODE),
                'value' => new FileValue('my_product', 'catalog', 'file_key_hash', 'my_file.jpg', null, null),
                'expected' => [self::TARGET_NAME => 'file_key_hash']
            ],
            'it selects the file path' => [
                'operations' => [],
                'selection' => new FilePathSelection(self::ATTRIBUTE_CODE),
                'value' => new FileValue('my_product', 'catalog', 'file_key_hash', 'my_file.jpg', null, null),
                'expected' => [self::TARGET_NAME => 'files/my_product/test_attribute/my_file.jpg']
            ],
            'it applies default value operation when value is null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ]),
                ],
                'selection' => new FileNameSelection(self::ATTRIBUTE_CODE),
                'value' => new NullValue(),
                'expected' => [self::TARGET_NAME => 'n/a']
            ],
            'it does not apply default value operation when value is not null' => [
                'operations' => [
                    DefaultValueOperation::createFromNormalized([
                        'value' => 'n/a'
                    ]),
                ],
                'selection' => new FileNameSelection(self::ATTRIBUTE_CODE),
                'value' => new FileValue('my_product', 'catalog', 'file_key_hash', 'my_file.jpg', null, null),
                'expected' => [self::TARGET_NAME => 'my_file.jpg']
            ],
        ];
    }
}
