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

use Akeneo\Platform\TailoredExport\Application\ProductMapper;
use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AttributeTestCase extends KernelTestCase
{
    public const ATTRIBUTE_CODE = 'test_attribute';
    public const TARGET_NAME = 'test_column';

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getProductMapper(): ProductMapper
    {
        return static::$container->get('Akeneo\Platform\TailoredExport\Application\ProductMapper');
    }

    protected function createSingleSourceColumnCollection(array $operations, SelectionInterface $selection): ColumnCollection
    {
        $sourceCollection = SourceCollection::create([
            new AttributeSource(
                'virtual_attribute_type',
                self::ATTRIBUTE_CODE,
                null,
                null,
                OperationCollection::create($operations),
                $selection
            )
        ]);
        $columnCollection = ColumnCollection::create([
            new Column(self::TARGET_NAME, $sourceCollection)
        ]);

        return $columnCollection;
    }

    protected function createSingleValueValueCollection(SourceValueInterface $value): ValueCollection
    {
        $valueCollection = new ValueCollection();
        $valueCollection->add($value, self::ATTRIBUTE_CODE, null, null);

        return $valueCollection;
    }
}
