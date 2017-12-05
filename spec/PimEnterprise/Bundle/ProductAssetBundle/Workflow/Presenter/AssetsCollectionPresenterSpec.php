<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Workflow\Presenter;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes as AssetAttributeType;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\Routing\RouterInterface;

class AssetsCollectionPresenterSpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $assetRepository, RouterInterface $router)
    {
        $this->beConstructedWith($assetRepository, $router);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_an_assets_collection(ValueInterface $productValue, AttributeInterface $frontView)
    {
        $productValue->getAttribute()->willReturn($frontView);
        $frontView->getType()->willReturn(AssetAttributeType::ASSETS_COLLECTION);
        $this->supports($productValue)->shouldBe(true);
    }

    function it_does_not_support_other_attribute_types(ValueInterface $productValue, AttributeInterface $frontView)
    {
        $productValue->getAttribute()->willReturn($frontView);
        $frontView->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $this->supports($productValue)->shouldBe(false);
    }

    function it_presents_assets_collection_changes(
        $assetRepository,
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

        $productValue->getData()->willReturn([$leather, $neoprene]);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('media');
        $this->present($productValue, ['data' => ['leather', 'kevlar']])->shouldReturn(
            [
                'before' => '<div class="AknThumbnail" style="background-image: url(\'leather/assetUrl\')"><span class="AknThumbnail-label">Awesome leather picture</span></div><div class="AknThumbnail" style="background-image: url(\'neoprene/assetUrl\')"><span class="AknThumbnail-label">Awesome neoprene picture</span></div>',
                'after' => '<div class="AknThumbnail" style="background-image: url(\'leather/assetUrl\')"><span class="AknThumbnail-label">Awesome leather picture</span></div><div class="AknThumbnail" style="background-image: url(\'kevlar/assetUrl\')"><span class="AknThumbnail-label">Awesome kevlar picture</span></div>'
            ]
        );
    }
}
