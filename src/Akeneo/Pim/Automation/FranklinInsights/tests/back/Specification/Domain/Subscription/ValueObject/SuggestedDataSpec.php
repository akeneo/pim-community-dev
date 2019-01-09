<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_a_suggested_data(): void
    {
        $this->shouldHaveType(SuggestedData::class);
    }

    public function it_is_iterable(): void
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    public function it_is_a_collection_of_suggested_values(): void
    {
        $this->beConstructedWith(
            [
                [
                    'pimAttributeCode' => 'color',
                    'value' => 'black',
                ],
                [
                    'pimAttributeCode' => 'fabric',
                    'value' => 'leather',
                ],
            ]
        );
        foreach ($this->getIterator() as $data) {
            $data->shouldBeAnInstanceOf(SuggestedValue::class);
        }
    }

    public function it_tells_if_it_is_empty(): void
    {
        $this->isEmpty()->shouldReturn(true);
    }

    public function it_tells_if_it_is_not_empty(): void
    {
        $this->beConstructedWith(
            [
                [
                    'pimAttributeCode' => 'color',
                    'value' => 'black',
                ],
            ]
        );
        $this->isEmpty()->shouldReturn(false);
    }

    public function it_gets_raw_values(): void
    {
        $suggestedValues = [
            [
                'pimAttributeCode' => 'color',
                'value' => 'black',
            ],
            [
                'pimAttributeCode' => 'fabric',
                'value' => 'leather',
            ],
        ];
        $this->beConstructedWith($suggestedValues);
        $this->getRawValues()->shouldReturn($suggestedValues);
    }

    public function it_returns_null_if_there_are_no_values(): void
    {
        $this->getRawValues()->shouldReturn(null);
    }
}
