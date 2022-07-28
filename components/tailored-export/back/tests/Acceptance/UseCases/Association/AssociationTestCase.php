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

use Akeneo\Platform\TailoredExport\Application\Common\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\TailoredExport\Application\Common\Format\ElementCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Format\SourceElement;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;
use Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AssociationTestCase extends KernelTestCase
{
    public const ASSOCIATION_TYPE_CODE = 'X_SELL';
    public const TARGET_NAME = 'test_column';

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function getMapValuesQueryHandler(): MapValuesQueryHandler
    {
        return static::getContainer()->get('Akeneo\Platform\TailoredExport\Application\MapValues\MapValuesQueryHandler');
    }

    protected function createSingleSourceColumnCollection(
        bool $isQuantified,
        array $operations,
        SelectionInterface $selection
    ): ColumnCollection {
        $sourceCollection = SourceCollection::create([
            new AssociationTypeSource(
                sprintf('%s-uuid', self::ASSOCIATION_TYPE_CODE),
                static::ASSOCIATION_TYPE_CODE,
                $isQuantified,
                OperationCollection::create($operations),
                $selection
            )
        ]);

        return ColumnCollection::create([
            new Column(
                self::TARGET_NAME,
                $sourceCollection,
                new ConcatFormat(
                    ElementCollection::create([new SourceElement(sprintf('%s-uuid', self::ASSOCIATION_TYPE_CODE))]),
                    false
                )
            )
        ]);
    }

    protected function createSingleValueValueCollection(SourceValueInterface $value): ValueCollection
    {
        $valueCollection = new ValueCollection();
        $valueCollection->add($value, static::ASSOCIATION_TYPE_CODE, null, null);

        return $valueCollection;
    }
}
