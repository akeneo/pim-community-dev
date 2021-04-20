<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class ReferenceEntityValuePresenterSpec extends ObjectBehavior
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
        $this->supports(ReferenceEntityType::REFERENCE_ENTITY)->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_presents_reference_entity_collection_change_using_the_injected_renderer() {
        $foo = RecordCode::fromString('foo');

        $this->present($foo, ['data' => 'bar', 'attribute' => 'description'])->shouldReturn([
            'before' => 'foo',
            'after' => 'bar',
        ]);
    }
}
