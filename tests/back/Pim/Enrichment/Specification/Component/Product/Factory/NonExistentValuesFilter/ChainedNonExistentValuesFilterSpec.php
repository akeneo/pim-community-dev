<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\NonExistentValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
        GetAttributes $getAttributes,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository
    ) {
        $ecommerce = new Channel();

        $enUS = new Locale();
        $enUS->setCode('en_US');
        $enUS->addChannel($ecommerce);

        $deDE = new Locale();
        $deDE->setCode('de_DE');

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deDE);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn(null);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);
        $channelRepository->findOneByIdentifier('tablet')->willReturn(null);

        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null, false, 'textarea', []);
        $color = new Attribute('color', AttributeTypes::OPTION_SIMPLE_SELECT, [], false, false, null, false, 'option', []);

        $getAttributes->forCodes(['attribute_that_does_not_exists'])->willReturn(['unknown_attribute' => null]);
        $getAttributes->forCodes(['color'])->willReturn(['color' => $color]);

        $getAttributes->forCodes(['description'])->willReturn(['description' => $description]);

        $this->beConstructedWith(
            [$filter1, $filter2],
            new EmptyValuesCleaner(),
            new TransformRawValuesCollections($getAttributes->getWrappedObject()),
            $localeRepository,
            $channelRepository
        );
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

        $this->filterAll($rawValuesCollection)->shouldBeLike(['productA' => [], '123' => []]);
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

    function it_filters_unknown_channel(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2
    ) {
        $rawValuesCollection = [
            'productA' => [
                'description' => [
                    'ecommerce' => [
                        '<all_locales>' => 'bar'
                    ],
                    'tablet' => [
                        '<all_locales>' => 'foo'
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
                            'ecommerce' => [
                                '<all_locales>' => 'bar'
                            ],
                            'tablet' => [
                                '<all_locales>' => 'foo'
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

        $this->filterAll($rawValuesCollection)->shouldBeLike([
            'productA' => [
                'description' => [
                    'ecommerce' => [
                        '<all_locales>' => 'bar'
                    ]
                ]
            ]
        ]);
    }

    function it_filters_unknown_and_deactivated_locales(
        NonExistentValuesFilter $filter1,
        NonExistentValuesFilter $filter2
    ) {
        $rawValuesCollection = [
            'productA' => [
                'description' => [
                    'ecommerce' => [
                        'en_US' => 'bar',
                        'fr_FR' => 'foo',
                        'de_DE' => 'baz',
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
                            'ecommerce' => [
                                'en_US' => 'bar',
                                'fr_FR' => 'foo',
                                'de_DE' => 'baz',
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

        $this->filterAll($rawValuesCollection)->shouldBeLike([
            'productA' => [
                'description' => [
                    'ecommerce' => [
                        'en_US' => 'bar'
                    ]
                ]
            ]
        ]);
    }
}
