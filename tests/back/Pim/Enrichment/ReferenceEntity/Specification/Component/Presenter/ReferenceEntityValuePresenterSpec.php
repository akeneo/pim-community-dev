<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class ReferenceEntityValuePresenterSpec extends ObjectBehavior
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
        $this->supportsChange(ReferenceEntityType::REFERENCE_ENTITY)->shouldBe(true);
        $this->supportsChange('other')->shouldBe(false);
    }

    function it_presents_reference_entity_collection_change_using_the_injected_renderer(
        RendererInterface $renderer,
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $foo = RecordCode::fromString('foo');

        $value->getData()->willReturn($foo);
        $value->getAttributeCode()->willReturn('simple_reference_entity');

        $attributeRepository->findOneByIdentifier('simple_reference_entity')->willReturn($attribute);

        $attribute->getType()->willReturn(ReferenceEntityType::REFERENCE_ENTITY);

        $renderer->renderDiff('foo', 'bar')->willReturn('diff between two record codes');

        $this->setRenderer($renderer);

        $this->present($value, ['data' => 'bar'])->shouldReturn('diff between two record codes');
    }
}
