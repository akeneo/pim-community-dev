<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use PhpSpec\ObjectBehavior;

class OptionsValueWithLinkedDataSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->beConstructedWith('collection', ['summer_2020'], null, null, [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ]
        ]);

        $this->shouldBeAnInstanceOf(OptionsValueWithLinkedData::class);
    }

    function it_returns_data()
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], null, null, [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $this->getData()->shouldReturn(['summer_2020', 'winter_2020']);
    }

    function it_returns_linked_data()
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], null, null, [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $this->getLinkedData()->shouldReturn([
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);
    }

    function it_checks_if_code_exist()
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], null, null, [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $this->hasCode('summer_2020')->shouldReturn(true);
        $this->hasCode('spring_2020')->shouldReturn(false);
    }

    function it_returns_codes()
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], null, null, [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $this->getOptionCodes()->shouldReturn(['summer_2020', 'winter_2020']);
    }

    function it_can_be_formatted_as_string()
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], null, null, [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $this->__toString()->shouldReturn('[summer_2020], [winter_2020]');
    }

    function it_compares_itself_to_a_same_options_value(OptionsValueWithLinkedData $sameOptionsValue)
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], 'ecommerce', 'en_US', [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $sameOptionsValue->getLocaleCode()->willReturn('en_US');
        $sameOptionsValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn(['summer_2020', 'winter_2020']);
        $sameOptionsValue->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
            'winter_2020' => ['attribute' => 'collection', 'code' => 'winter_2020', 'labels' => ['en_US' => 'Winter 2020', 'fr_FR' => 'Hiver 2020',]],
        ]);

        $this->isEqual($sameOptionsValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_same_options_value_without_options(OptionsValueWithLinkedData $sameOptionsValue)
    {
        $this->beConstructedWith('collection', [], 'ecommerce', 'en_US', []);

        $sameOptionsValue->getLocaleCode()->willReturn('en_US');
        $sameOptionsValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionsValue->getData()->willReturn([]);
        $sameOptionsValue->getLinkedData()->willReturn([]);

        $this->isEqual($sameOptionsValue)->shouldReturn(true);
    }

    function it_compares_itself_to_another_value_type(MetricValueInterface $metricValue)
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], 'ecommerce', 'en_US', [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $metricValue->getLocaleCode()->willReturn('en_US');
        $metricValue->getScopeCode()->willReturn('ecommerce');
        $metricValue->getData()->willReturn(['summer_2020', 'winter_2020']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_options_value(OptionsValueWithLinkedData $differentOptionsValue)
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], 'ecommerce', 'en_US', [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $differentOptionsValue->getLocaleCode()->willReturn('en_US');
        $differentOptionsValue->getScopeCode()->willReturn('mobile');
        $differentOptionsValue->getData()->willReturn(['summer_2020', 'spring_2020']);
        $differentOptionsValue->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
            'winter_2020' => ['attribute' => 'collection', 'code' => 'winter_2020', 'labels' => ['en_US' => 'Winter 2020', 'fr_FR' => 'Hiver 2020',]],
        ]);

        $this->isEqual($differentOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_same_options_value_with_different_number_of_options(OptionsValueWithLinkedData $otherOptionsValue)
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], 'ecommerce', 'en_US', [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $otherOptionsValue->getLocaleCode()->willReturn('en_US');
        $otherOptionsValue->getScopeCode()->willReturn('ecommerce');
        $otherOptionsValue->getData()->willReturn(['summer_2020', 'spring_2020']);
        $otherOptionsValue->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
        ]);

        $this->isEqual($otherOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_same_options_value_with_different_options(OptionsValueWithLinkedData $differentOptionsValue)
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], 'ecommerce', 'en_US', [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $differentOptionsValue->getLocaleCode()->willReturn('en_US');
        $differentOptionsValue->getScopeCode()->willReturn('ecommerce');
        $differentOptionsValue->getData()->willReturn(['summer_2020', 'spring_2020']);
        $differentOptionsValue->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
            'spring_2020' => ['attribute' => 'collection', 'code' => 'spring_2020', 'labels' => ['en_US' => 'Spring 2020', 'fr_FR' => 'Printemps 2020',]],
        ]);

        $this->isEqual($differentOptionsValue)->shouldReturn(false);
    }

    function it_compares_itself_to_differents_linked_data(
        OptionsValueWithLinkedData $differentLinkedData,
        OptionsValueWithLinkedData $anotherDifferentLinkedData,
        OptionsValueWithLinkedData $aThirdDifferentLinkedData)
    {
        $this->beConstructedWith('collection', ['summer_2020', 'winter_2020'], 'ecommerce', 'en_US', [
            'summer_2020' => [
                'attribute' => 'collection',
                'code' => 'summer_2020',
                'labels' => [
                    'en_US' => 'Summer 2020',
                    'fr_FR' => 'Eté 2020',
                ]
            ],
            'winter_2020' => [
                'attribute' => 'collection',
                'code' => 'winter_2020',
                'labels' => [
                    'en_US' => 'Winter 2020',
                    'fr_FR' => 'Hiver 2020',
                ]
            ],
        ]);

        $differentLinkedData->getLocaleCode()->willReturn('en_US');
        $differentLinkedData->getScopeCode()->willReturn('ecommerce');
        $differentLinkedData->getData()->willReturn(['summer_2020', 'winter_2020']);
        $differentLinkedData->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
            'spring_2020' => ['attribute' => 'collection', 'code' => 'winter_2020', 'labels' => ['en_US' => 'Winter 2020', 'fr_FR' => 'Hiver 2020',]],
        ]);

        $anotherDifferentLinkedData->getLocaleCode()->willReturn('en_US');
        $anotherDifferentLinkedData->getScopeCode()->willReturn('ecommerce');
        $anotherDifferentLinkedData->getData()->willReturn(['summer_2020', 'winter_2020']);
        $anotherDifferentLinkedData->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
            'winter_2020' => ['attribute' => 'collection', 'code' => 'winter_2020', 'labels' => ['en_US' => 'Winter 2020', 'fr_FR' => 'Printemps 2020',]],
        ]);

        $aThirdDifferentLinkedData->getLocaleCode()->willReturn('en_US');
        $aThirdDifferentLinkedData->getScopeCode()->willReturn('ecommerce');
        $aThirdDifferentLinkedData->getData()->willReturn(['summer_2020', 'winter_2020']);
        $aThirdDifferentLinkedData->getLinkedData()->willReturn([
            'summer_2020' => ['attribute' => 'collection', 'code' => 'summer_2020', 'labels' => ['en_US' => 'Summer 2020', 'fr_FR' => 'Eté 2020',]],
            'winter_2020' => ['attribute' => 'collection', 'code' => 'spring_2020', 'labels' => ['en_US' => 'Winter 2020', 'fr_FR' => 'Hiver 2020',]],
        ]);

        $this->isEqual($differentLinkedData)->shouldReturn(false);
        $this->isEqual($anotherDifferentLinkedData)->shouldReturn(false);
        $this->isEqual($aThirdDifferentLinkedData)->shouldReturn(false);
    }
}
