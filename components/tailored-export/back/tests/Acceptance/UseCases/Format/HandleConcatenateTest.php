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
use Akeneo\Platform\TailoredExport\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\TailoredExport\Application\Common\Format\ElementCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Format\SourceElement;
use Akeneo\Platform\TailoredExport\Application\Common\Format\TextElement;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Enabled\EnabledSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent\ParentCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Scalar\ScalarSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class HandleConcatenateTest extends KernelTestCase
{
    public const TARGET_NAME = 'test_column';

    private ?MapValuesQueryHandler $mapValuesQueryHandler = null;

    public function setUp(): void
    {
        self::bootKernel(['debug' => false]);
        $this->mapValuesQueryHandler = self::getContainer()->get('Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler');
    }

    public function test_it_can_concatenate_multiple_sources(): void
    {
        $columnCollection = ColumnCollection::create([
            new Column(
                self::TARGET_NAME,
                SourceCollection::create([
                    new AttributeSource(
                        'is_active-uuid',
                        'pim_catalog_boolean',
                        'is_active',
                        null,
                        null,
                        OperationCollection::create([]),
                        new BooleanSelection(),
                    ),
                    new PropertySource(
                        'enabled-uuid',
                        'enabled',
                        null,
                        null,
                        OperationCollection::create([]),
                        new EnabledSelection(),
                    ),
                ]),
                new ConcatFormat(ElementCollection::create([
                    new SourceElement('is_active-uuid'),
                    new SourceElement('enabled-uuid'),
                ]), true),
            ),
        ]);

        $valueCollection = new ValueCollection();
        $valueCollection->add(new EnabledValue(true), 'enabled', null, null);
        $valueCollection->add(new BooleanValue(false), 'is_active', null, null);

        $mappedProduct = $this->mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame([self::TARGET_NAME => '0 1'], $mappedProduct);
    }

    public function test_it_can_concatenate_a_single_source(): void
    {
        $sourceCollection = SourceCollection::create([
            new AttributeSource(
                'is_active-uuid',
                'pim_catalog_boolean',
                'is_active',
                null,
                null,
                OperationCollection::create([]),
                new BooleanSelection(),
            ),
        ]);

        $columnCollection = ColumnCollection::create([
            new Column(
                self::TARGET_NAME,
                $sourceCollection,
                new ConcatFormat(ElementCollection::create([
                    new SourceElement('is_active-uuid'),
                ]), true),
            ),
        ]);

        $valueCollection = new ValueCollection();
        $valueCollection->add(new BooleanValue(false), 'is_active', null, null);

        $mappedProduct = $this->mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame([self::TARGET_NAME => '0'], $mappedProduct);
    }

    public function test_it_can_concatenate_multiple_sources_with_text(): void
    {
        $columnCollection = ColumnCollection::create([
            new Column(
                self::TARGET_NAME,
                SourceCollection::create([
                    new AttributeSource(
                        'name-uuid',
                        'pim_catalog_text',
                        'name',
                        null,
                        null,
                        OperationCollection::create([]),
                        new ScalarSelection(),
                    ),
                    new PropertySource(
                        'parent-uuid',
                        'parent',
                        null,
                        null,
                        OperationCollection::create([]),
                        new ParentCodeSelection(),
                    ),
                ]),
                new ConcatFormat(ElementCollection::create([
                    new SourceElement('name-uuid'),
                    new TextElement('is a'),
                    new SourceElement('parent-uuid'),
                ]), true),
            ),
        ]);

        $valueCollection = new ValueCollection();
        $valueCollection->add(new StringValue('My hat'), 'name', null, null);
        $valueCollection->add(new ParentValue('red-hat'), 'parent', null, null);

        $mappedProduct = $this->mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame([self::TARGET_NAME => 'My hat is a red-hat'], $mappedProduct);
    }

    public function test_it_can_concatenate_multiple_sources_without_space_between(): void
    {
        $columnCollection = ColumnCollection::create([
            new Column(
                self::TARGET_NAME,
                SourceCollection::create([
                    new AttributeSource(
                        'name-uuid',
                        'pim_catalog_text',
                        'name',
                        null,
                        null,
                        OperationCollection::create([]),
                        new ScalarSelection(),
                    ),
                    new PropertySource(
                        'parent-uuid',
                        'parent',
                        null,
                        null,
                        OperationCollection::create([]),
                        new ParentCodeSelection(),
                    ),
                ]),
                new ConcatFormat(ElementCollection::create([
                    new SourceElement('name-uuid'),
                    new TextElement('/'),
                    new SourceElement('parent-uuid'),
                ]), false),
            ),
        ]);

        $valueCollection = new ValueCollection();
        $valueCollection->add(new StringValue('My hat'), 'name', null, null);
        $valueCollection->add(new ParentValue('red-hat'), 'parent', null, null);

        $mappedProduct = $this->mapValuesQueryHandler->handle(new MapValuesQuery($columnCollection, $valueCollection));

        Assert::assertSame([self::TARGET_NAME => 'My hat/red-hat'], $mappedProduct);
    }
}
