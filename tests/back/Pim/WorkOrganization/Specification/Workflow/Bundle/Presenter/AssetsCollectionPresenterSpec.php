<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Asset\Bundle\AttributeType\AttributeTypes as AssetAttributeType;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class AssetsCollectionPresenterSpec extends ObjectBehavior
{
    function let(
        AssetRepositoryInterface $assetRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        RouterInterface $router
    ) {
        $this->beConstructedWith($assetRepository, $attributeRepository, $router);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_an_assets_collection() {
        $this->supports(AssetAttributeType::ASSETS_COLLECTION)->shouldBe(true);
    }

    function it_does_not_support_other_attribute_types() {
        $this->supports(AttributeTypes::PRICE_COLLECTION)->shouldBe(false);
    }

    function it_presents_assets_collection_changes(
        $assetRepository,
        $attributeRepository,
        ValueInterface $productValue,
        AttributeInterface $attribute,
        AssetInterface $leather,
        AssetInterface $neoprene,
        AssetInterface $kevlar,
        VariationInterface $leatherVariation,
        VariationInterface $neopreneVariation,
        VariationInterface $kevlarVariation,
        ChannelInterface $ecommerce,
        LocaleInterface $en,
        $router
    ) {
        $assetRepository->findBy(['code' => ['leather', 'kevlar']])->willReturn([$leather, $kevlar]);
        $assetRepository->findBy(['code' => ['leather', 'neoprene']])->willReturn([$leather, $neoprene]);

        $leather->getVariations()->willReturn([$leatherVariation]);
        $leather->getDescription()->willReturn('Awesome leather picture');
        $leather->getCode()->willReturn('leather');
        $leatherVariation->getChannel()->willReturn($ecommerce);
        $ecommerce->getCode()->willReturn('ecommerce');
        $leatherVariation->getLocale()->willReturn($en);
        $en->getCode()->willReturn('en_US');
        $router->generate('pimee_product_asset_thumbnail', [
            'code' => 'leather',
            'filter' => 'thumbnail',
            'channelCode' => 'ecommerce',
            'localeCode' => 'en_US'
        ])->willReturn('leather/assetUrl');

        $neoprene->getVariations()->willReturn([$neopreneVariation]);
        $neoprene->getDescription()->willReturn('Awesome neoprene picture');
        $neoprene->getCode()->willReturn('neoprene');
        $neopreneVariation->getChannel()->willReturn($ecommerce);
        $ecommerce->getCode()->willReturn('ecommerce');
        $neopreneVariation->getLocale()->willReturn($en);
        $en->getCode()->willReturn('en_US');
        $router->generate('pimee_product_asset_thumbnail', [
            'code' => 'neoprene',
            'filter' => 'thumbnail',
            'channelCode' => 'ecommerce',
            'localeCode' => 'en_US'
        ])->willReturn('neoprene/assetUrl');

        $kevlar->getVariations()->willReturn([$kevlarVariation]);
        $kevlar->getDescription()->willReturn('Awesome kevlar picture');
        $kevlar->getCode()->willReturn('kevlar');
        $kevlarVariation->getChannel()->willReturn($ecommerce);
        $ecommerce->getCode()->willReturn('ecommerce');
        $kevlarVariation->getLocale()->willReturn($en);
        $en->getCode()->willReturn('en_US');
        $router->generate('pimee_product_asset_thumbnail', [
            'code' => 'kevlar',
            'filter' => 'thumbnail',
            'channelCode' => 'ecommerce',
            'localeCode' => 'en_US'
        ])->willReturn('kevlar/assetUrl');

        $productValue->getData()->willReturn(['leather', 'neoprene']);
        $productValue->getAttributeCode()->willReturn('media');
        $attributeRepository->findOneByIdentifier('media')->willReturn($attribute);
        $attribute->getCode()->willReturn('media');
        $this->present($productValue, ['data' => ['leather', 'kevlar']])->shouldReturn(
            [
                'before' => '<div class="AknThumbnail" style="background-image: url(\'leather/assetUrl\')"><span class="AknThumbnail-label">Awesome leather picture</span></div><div class="AknThumbnail" style="background-image: url(\'neoprene/assetUrl\')"><span class="AknThumbnail-label">Awesome neoprene picture</span></div>',
                'after' => '<div class="AknThumbnail" style="background-image: url(\'leather/assetUrl\')"><span class="AknThumbnail-label">Awesome leather picture</span></div><div class="AknThumbnail" style="background-image: url(\'kevlar/assetUrl\')"><span class="AknThumbnail-label">Awesome kevlar picture</span></div>'
            ]
        );
    }
}
