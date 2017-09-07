<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class DBDataCollectorSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        FamilyRepositoryInterface $familyRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith(
            $channelRepository,
            $productRepository,
            $localeRepository,
            $familyRepository,
            $userRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\DataCollector\DBDataCollector');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects_database_statistics(
        $channelRepository,
        $productRepository,
        $localeRepository,
        $familyRepository,
        $userRepository
    ) {
        $channelRepository->countAll()->willReturn(3);
        $productRepository->countAll()->willReturn(1121);
        $localeRepository->countAllActivated()->willReturn(3);
        $familyRepository->countAll()->willReturn(14);
        $userRepository->countAll()->willReturn(5);

        $this->collect()->shouldReturn(
            [
                "nb_channels"   => 3,
                "nb_locales"    => 3,
                "nb_products"   => 1121,
                "nb_families"   => 14,
                "nb_users"      => 5,
            ]
        );
    }
}
