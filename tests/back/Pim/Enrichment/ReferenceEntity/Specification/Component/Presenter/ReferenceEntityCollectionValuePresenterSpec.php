<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionValuePresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_is_not_a_translator_aware_presenter()
    {
        $this->shouldNotBeAnInstanceOf(TranslatorAwareInterface::class);
    }

    function it_supports_reference_entity_collection_value_type()
    {
        $this->supports(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION)->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_presents_reference_entity_collection_change_using_the_injected_renderer(RendererInterface $renderer)
    {
        $foo = RecordCode::fromString('foo');
        $bar = RecordCode::fromString('bar');
        $renderer->renderDiff(['foo', 'bar'], ['foo', 'bar', 'baz'])->willReturn('diff between two collections');
        $this->setRenderer($renderer);

        $this->present(['data' => ['foo', 'bar', 'baz']], [$foo, $bar])->shouldReturn('diff between two collections');
    }
}
