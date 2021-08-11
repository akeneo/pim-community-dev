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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Format;

use Akeneo\Platform\TailoredExport\Application\Common\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class HandleConcatenateTest extends KernelTestCase
{
    public const TARGET_NAME = 'test_column';

    private ?MapValuesQueryHandler $mapValuesQueryHandler;

    public function setUp(): void
    {
        self::bootKernel(['debug' => false]);
        $this->mapValuesQueryHandler = self::$container->get('Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler');
    }

    public function test_it_can_concatenate_multiple_sources(): void
    {
        $columnCollection = ColumnCollection::create([
            new Column(self::TARGET_NAME, SourceCollection::create([
                new AttributeSource(
                    'pim_catalog_boolean',
                    'is_active',
                    null,
                    null,
                    OperationCollection::create([]),
                    new BooleanSelection()
                ),
                new PropertySource(
                    'enabled',
                    OperationCollection::create([]),
                    new EnabledSelection()
                )
            ]))
        ]);

        $valueCollection = new ValueCollection();
        $valueCollection->add(new EnabledValue(true), 'enabled', null, null);
        $valueCollection->add(new BooleanValue(false), 'is_active', null, null);

        $mappedProduct = $this->mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame([
            self::TARGET_NAME => '0 1'
        ], $mappedProduct);
    }

    public function test_it_can_concatenate_a_single_source(): void
    {
        $sourceCollection = SourceCollection::create([
            new AttributeSource(
                'pim_catalog_boolean',
                'is_active',
                null,
                null,
                OperationCollection::create([]),
                new BooleanSelection()
            )
        ]);
        $columnCollection = ColumnCollection::create([
            new Column(self::TARGET_NAME, $sourceCollection)
        ]);

        $valueCollection = new ValueCollection();
        $valueCollection->add(new BooleanValue(false), 'is_active', null, null);

        $mappedProduct = $this->mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame([
            self::TARGET_NAME => '0'
        ], $mappedProduct);
    }
}
