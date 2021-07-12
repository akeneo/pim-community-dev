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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\UseCases\Association;

use Akeneo\Platform\TailoredExport\Application\ProductMapper;
use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AssociationTestCase extends KernelTestCase
{
    public const ASSOCIATION_TYPE_CODE = 'X_SELL';
    public const TARGET_NAME = 'test_column';

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getProductMapper(): ProductMapper
    {
        return static::$container->get('Akeneo\Platform\TailoredExport\Application\ProductMapper');
    }

    protected function createSingleSourceColumnCollection(bool $isQuantified, array $operations, SelectionInterface $selection): ColumnCollection
    {
        return ColumnCollection::create([
            new Column(self::TARGET_NAME, SourceCollection::create([
                new AssociationTypeSource(
                    static::ASSOCIATION_TYPE_CODE,
                    $isQuantified,
                    OperationCollection::create($operations),
                    $selection
                )
            ]))
        ]);
    }

    protected function createSingleValueValueCollection(SourceValueInterface $value): ValueCollection
    {
        $valueCollection = new ValueCollection();
        $valueCollection->add($value, static::ASSOCIATION_TYPE_CODE, null, null);

        return $valueCollection;
    }
}
