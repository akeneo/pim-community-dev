<?php

namespace spec\Pim\Component\Catalog\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class AttributeValuesResolverSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, ChannelRepositoryInterface $channelRepository)
    {
        $this->beConstructedWith($channelRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeValuesResolver::class);
        $this->shouldImplement(AttributeValuesResolverInterface::class);
    }

    function it_resolves_eligible_values_for_a_set_of_attributes(
        $localeRepository,
        $channelRepository,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $desc,
        AttributeInterface $tax,
        LocaleInterface $fr,
        LocaleInterface $en,
        ChannelInterface $ecom,
        ChannelInterface $print
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->isLocaleSpecific()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);
        $name->isLocaleSpecific()->willReturn(false);

        $desc->getCode()->willReturn('description');
        $desc->getType()->willReturn('pim_catalog_text');
        $desc->isLocalizable()->willReturn(true);
        $desc->isScopable()->willReturn(true);
        $desc->isLocaleSpecific()->willReturn(false);

        $tax->getCode()->willReturn('tax');
        $tax->getType()->willReturn('pim_catalog_text');
        $tax->isLocalizable()->willReturn(true);
        $tax->isScopable()->willReturn(false);
        $tax->isLocaleSpecific()->willReturn(true);
        $tax->getAvailableLocaleCodes()->willReturn(['fr_FR']);

        $fr->getCode()->willReturn('fr_FR');
        $en->getCode()->willReturn('en_US');
        $localeRepository->getActivatedLocales()->willReturn([$fr, $en]);

        $ecom->getCode()->willReturn('ecommerce');
        $ecom->getLocales()->willReturn([$en, $fr]);
        $print->getCode()->willReturn('print');
        $print->getLocales()->willReturn([$en, $fr]);
        $channelRepository->findAll()->willReturn([$ecom, $print]);

        $this->resolveEligibleValues([$sku, $name, $desc, $tax])->shouldReturn(
            [
                [
                    'attribute' => 'sku',
                    'type' => 'pim_catalog_identifier',
                    'locale' => null,
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => null
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'print'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => 'print'
                ],
                [
                    'attribute' => 'tax',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ]
            ]
        );
    }
}
