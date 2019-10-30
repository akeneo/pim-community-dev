<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChainedNonExistentValuesFilterSpec extends ObjectBehavior
{
    public function let(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2,
        GetAttributes $getAttributes
    ) {
        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null, false, 'textarea');
        $color = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false, 'option');

        $getAttributes->forCodes(['attribute_that_does_not_exists'])->willReturn(['unknown_attribute' => null]);
        $getAttributes->forCodes(['color'])->willReturn(['color' => $color]);

        $getAttributes->forCodes(['description'])->willReturn(['description' => $description]);

        $this->beConstructedWith([$filter1, $filter2], new EmptyValuesCleaner(), new TransformRawValuesCollections($getAttributes->getWrappedObject()));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChainedNonExistentValuesFilterInterface::class);
    }

    public function it_filters_raw_value_collection(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2
    ) {
        $rawValuesCollection = [
            'productA' => [
                'description' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'a description'
                    ]
                ]
            ]
        ];

        $nonFilterRawValues = [
            AttributeTypes::TEXTAREA => [
                'description' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'a description'
                            ]
                        ],
                        'properties' => []
                    ]
                ]
            ]
        ];

        $ongoingRawValues = new OnGoingFilteredRawValues([], $nonFilterRawValues);
        $filter1->filter($ongoingRawValues)->willReturn($ongoingRawValues);
        $filter2->filter($ongoingRawValues)->willReturn($ongoingRawValues);

        $this->filterAll($rawValuesCollection)->shouldBeLike($rawValuesCollection);
    }

    public function it_filters_empty_values(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2
    ) {
        $rawValuesCollection = [
            'productA' => [
                'description' => [
                    '<all_channels>' => [
                        '<all_locales>' => null
                    ]
                ]
            ]
        ];

        $nonFilterRawValues = [
            AttributeTypes::TEXTAREA => [
                'description' => [
                    [
                        'identifier' => 'productA',
                        'values' => [
                            '<all_channels>' => [
                                '<all_locales>' => null
                            ]
                        ],
                        'properties' => []
                    ]
                ]
            ]
        ];

        $ongoingRawValues = new OnGoingFilteredRawValues([], $nonFilterRawValues);
        $filter1->filter($ongoingRawValues)->willReturn($ongoingRawValues);
        $filter2->filter($ongoingRawValues)->willReturn($ongoingRawValues);

        $this->filterAll($rawValuesCollection)->shouldBeLike(['productA' => []]);
    }


    function it_filters_unknown_attribute(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2
    ) {
        $rawValuesCollection = [
            'productA' => [
                'attribute_that_does_not_exists' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar'
                    ]
                ]
            ]
        ];

        $ongoingRawValues = new OnGoingFilteredRawValues([], []);
        $filter1->filter($ongoingRawValues)->willReturn($ongoingRawValues);
        $filter2->filter($ongoingRawValues)->willReturn($ongoingRawValues);

        $this->filterAll($rawValuesCollection)->shouldBeLike(['productA' => []]);
    }
}
