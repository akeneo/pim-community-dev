<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Provider;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Provider\ReferenceEntityCollectionProvider;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionProviderSpec extends ObjectBehavior {
    function it_is_initializable()
    {
        $this->shouldHaveType(FieldProviderInterface::class);
        $this->shouldHaveType(EmptyValueProviderInterface::class);
        $this->shouldHaveType(ReferenceEntityCollectionProvider::class);
    }

    function it_provides_an_empty_value(AttributeInterface $designer)
    {
        $this->getEmptyValue($designer)->shouldReturn([]);
    }

    function it_provides_a_field(AttributeInterface $designer)
    {
        $this->getField($designer)->shouldReturn('akeneo-reference-entity-collection-field');
    }

    function it_supports_a_reference_entity_attribute(AttributeInterface $designer, AttributeInterface $sku)
    {
        $designer->getType()->willReturn('akeneo_reference_entity_collection');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $this->supports($designer)->shouldReturn(true);
        $this->supports($sku)->shouldReturn(false);
    }
}
