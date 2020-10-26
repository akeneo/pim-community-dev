<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Presenter;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use PhpSpec\ObjectBehavior;

class AssetCollectionValuePresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_is_not_a_translator_aware_presenter()
    {
        $this->shouldNotBeAnInstanceOf(TranslatorAwareInterface::class);
    }

    function it_supports_asset_collection_value_type()
    {
        $this->supports(AssetCollectionType::ASSET_COLLECTION)->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_presents_asset_collection_change_using_the_injected_renderer(RendererInterface $renderer)
    {
        $foo = AssetCode::fromString('foo');
        $bar = AssetCode::fromString('bar');
        $renderer->renderDiff(['foo', 'bar'], ['foo', 'bar', 'baz'])->willReturn('diff between two collections');
        $this->setRenderer($renderer);

        $this->present([$foo, $bar], ['data' => ['foo', 'bar', 'baz']])->shouldReturn('diff between two collections');
    }

    function it_presents_without_error_old_null_data(RendererInterface $renderer)
    {
        $this->setRenderer($renderer);
        $renderer->renderDiff([], ['foo', 'bar', 'baz'])->willReturn([]);
        $this->present(null, ['data' => ['foo', 'bar', 'baz']])->shouldReturn([]);
    }
}
