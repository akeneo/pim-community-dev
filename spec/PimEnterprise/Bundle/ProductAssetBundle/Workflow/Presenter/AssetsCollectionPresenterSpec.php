<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Workflow\Presenter;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

class AssetsCollectionPresenterSpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $assetRepository)
    {
        $this->beConstructedWith($assetRepository);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_an_assets_collection()
    {
        $this->supportsChange('pim_assets_collection')->shouldBe(true);
    }

    function it_does_not_support_other_attribute_types()
    {
        $this->supportsChange('pim_reference_data_simpleselect')->shouldBe(false);
    }

    function it_presents_assets_collection_change_using_the_injected_renderer(
        $assetRepository,
        RendererInterface $renderer,
        ProductValueInterface $productValue,
        AssetInterface $leather,
        AssetInterface $neoprene,
        AssetInterface $kevlar
    ) {
        $leather->__toString()->willReturn('Leather');
        $neoprene->__toString()->willReturn('[Neoprene]');
        $kevlar->__toString()->willReturn('Kevlar');

        $assetRepository->findBy(['code' => ['Leather', 'Kevlar']])->willReturn([$leather, $kevlar]);

        $renderer->renderDiff(['Leather', '[Neoprene]'], ['Leather', 'Kevlar'])->willReturn('diff between two assets collection');
        $this->setRenderer($renderer);

        $productValue->getData()->willReturn([$leather, $neoprene]);
        $this->present($productValue, ['data' => ['Leather', 'Kevlar']])->shouldReturn('diff between two assets collection');
    }
}
