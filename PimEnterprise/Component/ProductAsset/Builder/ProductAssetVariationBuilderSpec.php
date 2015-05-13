<?php

namespace spec\PimEnterprise\Component\ProductAsset\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use Prophecy\Argument;

class ProductAssetVariationBuilderSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $repository,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        LocaleInterface $en_US,
        LocaleInterface $fr_FR,
        LocaleInterface $de_DE
    ) {
        $ecommerce->getLocales()->willReturn([$fr_FR]);
        $print->getLocales()->willReturn([$de_DE, $en_US]);
        $channels = [$ecommerce, $print];
        $repository->getFullChannels()->willReturn($channels);

        $this->beConstructedWith($repository);
    }

    function it_builds_missing_variations($ecommerce, $print, $en_US, $fr_FR, $de_DE, ProductAssetInterface $asset)
    {
        $asset->hasVariation($ecommerce, $fr_FR)->willReturn(false);
        $asset->hasVariation($print, $en_US)->willReturn(true);
        $asset->hasVariation($print, $de_DE)->willReturn(false);

        $missings = $this->buildMissing($asset);
        $missings->shouldHaveCount(2);
        $missings->shouldBeArrayOfVariations();
    }

    function it_builds_a_variation(ProductAssetInterface $asset, $en_US, $ecommerce)
    {
        $variation = $this->buildOne($asset, $ecommerce, $en_US);

        $variation->getAsset()->shouldBe($asset);
        $variation->getChannel()->shouldBe($ecommerce);
        $variation->getLocale()->shouldBe($en_US);
    }

    function it_builds_all_variations(ProductAssetInterface $asset)
    {
        $missings = $this->buildAll($asset);
        $missings->shouldHaveCount(3);
        $missings->shouldBeArrayOfVariations();
    }

    public function getMatchers()
    {
        return [
            'beArrayOfVariations' => function ($subject) {
                foreach ($subject as $row) {
                    if (!$row instanceof ProductAssetVariationInterface) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
