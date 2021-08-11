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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Property;

use Akeneo\Platform\TailoredExport\Application\Common\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class PropertyTestCase extends KernelTestCase
{
    public const PROPERTY_NAME = 'property_name';
    public const TARGET_NAME = 'test_column';

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getMapValuesQueryHandler(): MapValuesQueryHandler
    {
        return static::$container->get('Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler');
    }

    protected function createSingleSourceColumnCollection(array $operations, SelectionInterface $selection): ColumnCollection
    {
        $columnCollection = ColumnCollection::create([
            new Column(self::TARGET_NAME, SourceCollection::create([
                new PropertySource(
                    static::PROPERTY_NAME,
                    OperationCollection::create($operations),
                    $selection
                )
            ]))
        ]);

        return $columnCollection;
    }

    protected function createSingleValueValueCollection(SourceValueInterface $value): ValueCollection
    {
        $valueCollection = new ValueCollection();
        $valueCollection->add($value, static::PROPERTY_NAME, null, null);

        return $valueCollection;
    }
}
