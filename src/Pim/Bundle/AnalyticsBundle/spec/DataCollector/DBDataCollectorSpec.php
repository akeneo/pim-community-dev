<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CountableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\AnalyticsBundle\DataCollector\DBDataCollector;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class DBDataCollectorSpec extends ObjectBehavior
{
    function let(
        CountableRepositoryInterface $channelRepository,
        CountableRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        CountableRepositoryInterface $familyRepository,
        CountableRepositoryInterface $userRepository,
        CountableRepositoryInterface $productModelRepository,
        CountableRepositoryInterface $variantProductRepository,
        CountableRepositoryInterface $familyVariantRepository
    ) {
        $this->beConstructedWith(
            $channelRepository,
            $productRepository,
            $localeRepository,
            $familyRepository,
            $userRepository,
            $productModelRepository,
            $variantProductRepository,
            $familyVariantRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DBDataCollector::class);
        $this->shouldHaveType(DataCollectorInterface::class);
    }

    function it_collects_database_statistics(
        $channelRepository,
        $productRepository,
        $localeRepository,
        $familyRepository,
        $userRepository,
        $productModelRepository,
        $variantProductRepository,
        $familyVariantRepository
    ) {
        $channelRepository->countAll()->willReturn(3);
        $productRepository->countAll()->willReturn(1121);
        $localeRepository->countAllActivated()->willReturn(3);
        $familyRepository->countAll()->willReturn(14);
        $userRepository->countAll()->willReturn(5);
        $productModelRepository->countAll()->willReturn(123);
        $variantProductRepository->countAll()->willReturn(89);
        $familyVariantRepository->countAll()->willReturn(2);

        $this->collect()->shouldReturn(
            [
                "nb_channels"           => 3,
                "nb_locales"            => 3,
                "nb_products"           => 1121,
                'nb_product_models'     => 123,
                'nb_variant_products'   => 89,
                'nb_family_variants'    => 2,
                "nb_families"           => 14,
                "nb_users"              => 5
            ]
        );
    }
}
