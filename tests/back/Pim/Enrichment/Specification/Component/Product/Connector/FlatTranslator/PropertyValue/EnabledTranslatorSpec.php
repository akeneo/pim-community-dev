<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PhpSpec\ObjectBehavior;

class EnabledTranslatorSpec extends ObjectBehavior
{
    function let(
        LabelTranslatorInterface $labelTranslator
    ) {
        $this->beConstructedWith($labelTranslator);
    }

    function it_only_supports_enabled_property()
    {
        $this->supports('enabled')->shouldReturn(true);
        $this->supports('categories')->shouldReturn(false);
        $this->supports('something_else')->shouldReturn(false);
    }

    function it_translates_enabled_property_values(
        LabelTranslatorInterface $labelTranslator
    ) {
        $labelTranslator->translate('pim_common.yes', 'fr_FR', '[yes]')->willReturn('Oui');
        $labelTranslator->translate('pim_common.yes', 'UNKNOWN', '[yes]')->willReturn('[yes]');
        $labelTranslator->translate('pim_common.no', 'fr_FR', '[no]')->willReturn('Non');
        $labelTranslator->translate('pim_common.no', 'UNKNOWN', '[no]')->willReturn('[no]');

        $this->translate([true, false, true], 'fr_FR', 'ecommerce')->shouldReturn(['Oui', 'Non', 'Oui']);
        $this->translate([false, true, false], 'UNKNOWN', 'ecommerce')->shouldReturn(['[no]', '[yes]', '[no]']);
    }
}
