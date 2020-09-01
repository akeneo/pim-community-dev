<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use PhpSpec\ObjectBehavior;

class AttributeValueRegistrySpec extends ObjectBehavior
{
    function it_returns_translators_and_null_if_not_found(FlatAttributeValueTranslatorInterface $translator) {
        $translator->supports('pim_catalog_boolean', 'a-column')->willReturn(true);
        $translator->supports('X_SELL-products', 'another-column')->willReturn(false);
        
        $this->addTranslator($translator);
        $this->getTranslator('pim_catalog_boolean', 'a-column')->shouldReturn($translator);
        $this->getTranslator('X_SELL-products', 'another-column')->shouldReturn(null);
    }
}
