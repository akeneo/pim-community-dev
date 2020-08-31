<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PhpSpec\ObjectBehavior;

class BooleanTranslatorSpec extends ObjectBehavior
{
    function let(
        LabelTranslatorInterface $labelTranslator
    ) {
        $this->beConstructedWith($labelTranslator);
    }

    function it_only_supports_boolean_attributes()
    {
        $this->supports('pim_catalog_boolean', 'name')->shouldReturn(true);
        $this->supports('pim_catalog_multiselect', 'name')->shouldReturn(false);
        $this->supports('something_else', 'name')->shouldReturn(false);
    }

    function it_translates_boolean_attribute_values(
        LabelTranslatorInterface $labelTranslator
    ) {
        $labelTranslator->translate('pim_common.yes', 'fr_FR', '[yes]')->willReturn('Oui');
        $labelTranslator->translate('pim_common.yes', 'UNKNOWN', '[yes]')->willReturn('[yes]');
        $labelTranslator->translate('pim_common.no', 'fr_FR', '[no]')->willReturn('Non');
        $labelTranslator->translate('pim_common.no', 'UNKNOWN', '[no]')->willReturn('[no]');

        $this->translate('with', [], ['1', '0', '1', ''], 'fr_FR')->shouldReturn(['Oui', 'Non', 'Oui', '']);
        $this->translate('with', [], ['0', '1', '0', ''], 'UNKNOWN')->shouldReturn(['[no]', '[yes]', '[no]', '']);
    }
}
