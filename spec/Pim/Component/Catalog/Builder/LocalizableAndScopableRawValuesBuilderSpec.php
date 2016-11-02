<?php

namespace spec\Pim\Component\Catalog\Builder;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class LocalizableAndScopableRawValuesBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Builder\LocalizableAndScopableRawValuesBuilder');
    }

    function let(
        CachedObjectRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($attributeRepository, $channelRepository, $localeRepository);
    }

    function it_adds_missing_localizable_raw_values(
        $attributeRepository,
        $channelRepository,
        $localeRepository,
        AttributeInterface $attribute,
        LocaleInterface $fr,
        LocaleInterface $en
    ) {
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attribute->getCode()->willReturn('description');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);

        $channelRepository->findAll()->willReturn([]);
        $localeRepository->getActivatedLocales()->willReturn([$fr, $en]);
        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');

        $this->addMissing(
            [
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => 'just a description for en_US'
                    ]
                ]
            ]
        )->shouldReturn(
            [
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => 'just a description for en_US'
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => null
                    ],
                ]
            ]
        );
    }

    function it_adds_missing_scopable_raw_values(
        $attributeRepository,
        $channelRepository,
        $localeRepository,
        AttributeInterface $attribute,
        ChannelInterface $print,
        ChannelInterface $ecommerce
    ) {
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attribute->getCode()->willReturn('description');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $channelRepository->findAll()->willReturn([$print, $ecommerce]);
        $print->getCode()->willReturn('print');
        $ecommerce->getCode()->willReturn('ecommerce');
        $print->getLocales()->willReturn(new ArrayCollection([]));
        $ecommerce->getLocales()->willReturn(new ArrayCollection([]));
        $localeRepository->getActivatedLocales()->willReturn([]);

        $this->addMissing(
            [
                'description' => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => 'just a description for ecommerce'
                    ]
                ]
            ]
        )->shouldReturn(
            [
                'description' => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => 'just a description for ecommerce'
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'print',
                        'data'   => null
                    ]
                ]
            ]
        );
    }

    function it_adds_missing_localizable_and_scopable_raw_values(
        $attributeRepository,
        $channelRepository,
        $localeRepository,
        AttributeInterface $attribute,
        LocaleInterface $fr,
        LocaleInterface $en,
        LocaleInterface $de,
        ArrayCollection $printLocales,
        ArrayCollection $ecommerceLocales,
        ChannelInterface $print,
        ChannelInterface $ecommerce
    ) {
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attribute->getCode()->willReturn('description');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);

        $channelRepository->findAll()->willReturn([$print, $ecommerce]);
        $print->getCode()->willReturn('print');
        $printLocales->toArray()->willReturn([$fr]);
        $print->getLocales()->willReturn($printLocales);
        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerceLocales->toArray()->willReturn([$fr]);
        $ecommerce->getLocales()->willReturn($ecommerceLocales);
        $localeRepository->getActivatedLocales()->willReturn([$fr, $en, $de]);
        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');

        $this->addMissing(
            [
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'just a description for ecommerce en_US'
                    ]
                ]
            ]
        )->shouldReturn(
            [
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'just a description for ecommerce en_US'
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'print',
                        'data'   => null
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => null
                    ],
                ]
            ]
        );
    }
}
