<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PhpSpec\ObjectBehavior;

class PropertyTranslatorSpec extends ObjectBehavior
{
    function let(LabelTranslatorInterface $translator)
    {
        $this->beConstructedWith($translator, ['categories', 'family_variant', 'enabled', 'family', 'parent', 'groups']);
    }

    function it_translates_static_properties($translator)
    {
        $translator->translate('pim_common.categories', 'fr_FR', '[categories]')->willReturn('Catégories');

        $this->translate('categories', 'fr_FR')->shouldReturn('Catégories');
    }

    function it_supports_pim_default_properties()
    {
        $this->supports('categories')->shouldReturn(true);
        $this->supports('family_variant')->shouldReturn(true);
        $this->supports('enabled')->shouldReturn(true);
        $this->supports('family')->shouldReturn(true);
        $this->supports('parent')->shouldReturn(true);
        $this->supports('groups')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }
}
