<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentChannelLocaleValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
        NonExistentChannelLocaleValuesFilter $nonExistentChannelLocaleValuesFilter,
        GetAttributes $getAttributes
    ) {
        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null, null, false, 'textarea', []);
        $name = new Attribute('name', AttributeTypes::TEXT, [], true, true, null, null, false, 'text', []);
        $color = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, null, false, 'option', []);

        $getAttributes->forCodes(['attribute_that_does_not_exists'])->willReturn(['unknown_attribute' => null]);
        $getAttributes->forCodes(['color'])->willReturn(['color' => $color]);

        $getAttributes->forCodes(['description'])->willReturn(['description' => $description]);
        $getAttributes->forCodes(['description', 'name'])->willReturn(['description' => $description, 'name' => $name]);

        $this->beConstructedWith(
            [$filter1, $filter2],
            $nonExistentChannelLocaleValuesFilter,
            new EmptyValuesCleaner(),
            new TransformRawValuesCollections($getAttributes->getWrappedObject())
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChainedNonExistentValuesFilterInterface::class);
    }

    public function it_filters_raw_value_collection(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2,
        NonExistentChannelLocaleValuesFilter $nonExistentChannelLocaleValuesFilter
    ) {
        $rawValuesCollection = [
            'productA' => [
                'description' => ['<all_channels>' => ['<all_locales>' => 'a description']],
                'name' => ['<all_channels>' => ['<all_locales>' => 'a name']],
            ],
        ];

        $textareaValues = [AttributeTypes::TEXTAREA => [
            'description' => [
                [
                    'identifier' => 'productA',
                    'values' => ['<all_channels>' => ['<all_locales>' => 'a description']],
                    'properties' => [],
                ],
            ],
        ]];
        $textValues = [AttributeTypes::TEXT => [
            'name' => [
                [
                    'identifier' => 'productA',
                    'values' => ['<all_channels>' => ['<all_locales>' => 'a name']],
                    'properties' => [],
                ],
            ],
        ]];

        $nonFilterRawValues = \array_merge($textareaValues, $textValues);

        $ongoingRawValues = new OnGoingFilteredRawValues([], $nonFilterRawValues);
        $ongoingRawValuesAfterFilter1 = new OnGoingFilteredRawValues($textValues, $textareaValues);
        $filter1->filter($ongoingRawValues)->shouldBeCalledOnce()->willReturn($ongoingRawValuesAfterFilter1);
        $ongoingRawValuesAfterFilter2 = new OnGoingFilteredRawValues(\array_merge($textareaValues, $textValues), []);
        $filter2->filter($ongoingRawValuesAfterFilter1)->shouldBeCalledOnce()->willReturn($ongoingRawValuesAfterFilter2);

        $nonExistentChannelLocaleValuesFilter->filter($ongoingRawValues)
            ->shouldBeCalledOnce()->willReturn($ongoingRawValues);

        $this->filterAll($rawValuesCollection)->shouldBeLike($rawValuesCollection);
    }

    public function it_filters_empty_values(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2,
        NonExistentChannelLocaleValuesFilter $nonExistentChannelLocaleValuesFilter
    ) {
        $rawValuesCollection = [
            'productA' => [
                'description' => [
                    '<all_channels>' => [
                        '<all_locales>' => null
                    ]
                ]
            ],
            '123' => [
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
                    ],
                    [
                        'identifier' => '123',
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
        $nonExistentChannelLocaleValuesFilter->filter($ongoingRawValues)
            ->shouldBeCalledOnce()->willReturn($ongoingRawValues);

        $this->filterAll($rawValuesCollection)->shouldBeLike(['productA' => [], '123' => []]);
    }


    function it_filters_unknown_attribute(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2,
        NonExistentChannelLocaleValuesFilter $nonExistentChannelLocaleValuesFilter
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
        $nonExistentChannelLocaleValuesFilter->filter($ongoingRawValues)
            ->shouldBeCalledOnce()->willReturn($ongoingRawValues);

        $this->filterAll($rawValuesCollection)->shouldBeLike(['productA' => []]);
    }
}
