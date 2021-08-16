<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValue\Price;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\PriceCollectionValue;
use PhpSpec\ObjectBehavior;

class PriceCollectionValueSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith(
            [
                new Price('11', 'EUR'),
                new Price('1234', 'USD'),
            ]
        );

        $this->shouldBeAnInstanceOf(PriceCollectionValue::class);
    }

    public function it_throws_an_exception_if_price_collection_is_invalid()
    {
        $this->beConstructedWith(
            [
                new Price('11', 'EUR'),
                2,
                'asset_code_3'
            ],
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_price_collection()
    {
        $price1 = new Price('11', 'EUR');
        $price2 = new Price('1234', 'USD');

        $this->beConstructedWith([$price1, $price2]);

        $this->getPriceCollection()->shouldReturn([$price1, $price2]);
    }
}
