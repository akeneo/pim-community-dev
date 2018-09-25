<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\Provider;

use Akeneo\Pim\EnrichedEntity\Component\Provider\EnrichedEntityProvider;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Prophecy\Argument;

class EnrichedEntityProviderSpec extends ObjectBehavior {
    function it_is_initializable()
    {
        $this->shouldHaveType(FieldProviderInterface::class);
        $this->shouldHaveType(EmptyValueProviderInterface::class);
        $this->shouldHaveType(EnrichedEntityProvider::class);
    }

    function it_provides_an_empty_value(AttributeInterface $designer)
    {
        $this->getEmptyValue($designer)->shouldReturn([]);
    }

    function it_provides_a_field(AttributeInterface $designer)
    {
        $this->getField($designer)->shouldReturn('akeneo-enriched-entity-field');
    }

    function it_supports_an_enriched_entity_attribute(AttributeInterface $designer, AttributeInterface $sku)
    {
        $designer->getType()->willReturn('akeneo_enriched_entity_collection');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $this->supports($designer)->shouldReturn(true);
        $this->supports($sku)->shouldReturn(false);
    }
}
