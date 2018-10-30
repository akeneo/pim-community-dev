<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;

class ValueConverterRegistrySpec extends ObjectBehavior
{
    function it_increments_priority_if_service_already_registered(
        AttributeInterface $attribute,
        AbstractValueConverter $converter1,
        AbstractValueConverter $converter2
    ) {
        $converter1->supportsAttribute($attribute)->willReturn(true);
        $converter2->supportsAttribute($attribute)->willReturn(true);

        $this->register($converter1, 100);
        $this->register($converter2, 100);

        $this->getConverter($attribute)->shouldReturn($converter1);
    }

    function it_gets_converters(AttributeInterface $attribute, AbstractValueConverter $converter)
    {
        $converter->supportsAttribute($attribute)->willReturn(true);
        $this->register($converter, 100);

        $this->getConverter($attribute)->shouldReturn($converter);
    }

    function it_does_not_find_supported_converters(AttributeInterface $attribute, AbstractValueConverter $converter)
    {
        $converter->supportsAttribute($attribute)->willReturn(false);
        $this->register($converter, 100);

        $this->getConverter($attribute)->shouldReturn(null);
    }
}
