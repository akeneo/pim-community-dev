<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

class AttributeValuesResolverSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, ChannelRepositoryInterface $channelRepository)
    {
        $this->beConstructedWith($channelRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver');
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
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->isLocaleSpecific()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);
        $name->isLocaleSpecific()->willReturn(false);

        $desc->getCode()->willReturn('description');
        $desc->getAttributeType()->willReturn('pim_catalog_text');
        $desc->isLocalizable()->willReturn(true);
        $desc->isScopable()->willReturn(true);
        $desc->isLocaleSpecific()->willReturn(false);

        $tax->getCode()->willReturn('tax');
        $tax->getAttributeType()->willReturn('pim_catalog_text');
        $tax->isLocalizable()->willReturn(true);
        $tax->isScopable()->willReturn(false);
        $tax->isLocaleSpecific()->willReturn(true);
        $tax->getLocaleSpecificCodes()->willReturn(['fr_FR']);

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
