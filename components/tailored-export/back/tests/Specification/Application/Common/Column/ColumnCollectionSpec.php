<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\Column;

use Akeneo\Platform\TailoredExport\Application\Common\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceInterface;
use PhpSpec\ObjectBehavior;

class ColumnCollectionSpec extends ObjectBehavior
{
    public function it_returns_all_sources(
        Column $column1,
        Column $column2,
        Column $column3,
        SourceCollection $sourceCollection1,
        SourceCollection $sourceCollection2,
        SourceCollection $sourceCollection3,
        SourceInterface $sku,
        SourceInterface $name,
        SourceInterface $description,
    ): void {
        $column1->getSourceCollection()->willReturn($sourceCollection1);
        $column2->getSourceCollection()->willReturn($sourceCollection2);
        $column3->getSourceCollection()->willReturn($sourceCollection3);
        $sourceCollection1->getIterator()->willReturn(new \ArrayIterator([$sku->getWrappedObject()]));
        $sourceCollection2->getIterator()->willReturn(new \ArrayIterator([$name->getWrappedObject()]));
        $sourceCollection3->getIterator()->willReturn(new \ArrayIterator([$description->getWrappedObject()]));

        $this->beConstructedThrough('create', [[
            $column1,
            $column2,
            $column3,
        ]]);

        $this->getAllSources()->shouldBeLike(SourceCollection::create([
            $sku->getWrappedObject(),
            $name->getWrappedObject(),
            $description->getWrappedObject(),
        ]));
    }

    public function it_cannot_be_instantiated_without_columns(): void
    {
        $this->beConstructedThrough('create', [[]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
