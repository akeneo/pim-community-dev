<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChainedNonExistentValuesFilterSpec extends ObjectBehavior
{
    public function let(NonExistentValuesFilter $filter1, NonExistentValuesFilter $filter2)
    {
        $this->beConstructedWith([$filter1, $filter2]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChainedNonExistentValuesFilterInterface::class);
    }

    public function it_calls_every_values_filter_and_it_copies_the_remaining_values(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2
    ) {
        $nonFilterRawValues = [
            AttributeTypes::TEXT => [
                'description' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'a description'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $ongoingRawValues = new OnGoingFilteredRawValues([], $nonFilterRawValues);
        $filter1->filter($ongoingRawValues)->willReturn($ongoingRawValues);
        $filter2->filter($ongoingRawValues)->willReturn($ongoingRawValues);

        $onGoingFilteredRawValues = new OnGoingFilteredRawValues($nonFilterRawValues, []);

        $this->filterAll($ongoingRawValues)->shouldBeLike($onGoingFilteredRawValues);
    }
}
