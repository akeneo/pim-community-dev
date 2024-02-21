<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;
use PhpSpec\ObjectBehavior;

class HeaderRegistrySpec extends ObjectBehavior
{
    function it_returns_translators_and_null_if_not_found(FlatHeaderTranslatorInterface $translator) {
        $translator->supports('categories')->willReturn(true);
        $translator->supports('X_SELL-products')->willReturn(false);
        
        $this->addTranslator($translator);
        $this->getTranslator('categories')->shouldReturn($translator);
        $this->getTranslator('X_SELL-products')->shouldReturn(null);
    }
}
