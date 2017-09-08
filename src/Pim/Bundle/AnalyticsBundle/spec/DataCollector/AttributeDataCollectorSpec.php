<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\AnalyticsBundle\DataCollector\AttributeDataCollector;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountLocalizableAttribute;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountScopableAndLocalizableAttribute;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountScopableAttribute;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class AttributeDataCollectorSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        CountLocalizableAttribute $countLocalizableAttribute,
        CountScopableAttribute $countScopableAttribute,
        CountScopableAndLocalizableAttribute $countScopableAndLocalizableAttribute
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $countLocalizableAttribute,
            $countScopableAttribute,
            $countScopableAndLocalizableAttribute
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeDataCollector::class);
    }

    function it_is_a_data_collector()
    {
        $this->shouldImplement(DataCollectorInterface::class);
    }

    function it_collects_data_about_catalog(
        $attributeRepository,
        $countLocalizableAttribute,
        $countScopableAttribute,
        $countScopableAndLocalizableAttribute
    ) {
        $attributeRepository->countAll()->willReturn(1000);
        $countLocalizableAttribute->__invoke()->willReturn(33);
        $countScopableAttribute->__invoke()->willReturn(40);
        $countScopableAndLocalizableAttribute->__invoke()->willReturn(64);

        $this->collect()->shouldReturn([
            'nb_attributes' => 1000,
            'nb_scopable_attributes' => 40,
            'nb_localizable_attributes' => 33,
            'nb_scopable_localizable_attributes' => 64,
        ]);
    }
}
