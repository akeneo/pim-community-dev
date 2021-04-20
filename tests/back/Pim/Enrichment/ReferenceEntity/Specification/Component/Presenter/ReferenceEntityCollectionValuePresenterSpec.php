<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionValuePresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
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

    function it_presents_reference_entity_collection_change_using_the_injected_renderer()
    {
        $foo = RecordCode::fromString('foo');
        $bar = RecordCode::fromString('bar');

        $this->present([$foo, $bar], ['data' => ['foo', 'bar', 'baz']])->shouldReturn([
            'before' => ['foo', 'bar'],
            'after' => ['foo', 'bar', 'baz'],
        ]);
    }
}
