<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PhpSpec\ObjectBehavior;

class PropertyTranslatorSpec extends ObjectBehavior
{
    function let(LabelTranslatorInterface $translator)
    {
        $this->beContructedWith($translator);
    }

    function it_translate_static_properties($translator)
    {
        $translator->translate('pim_common.categories')->willReturn('Catégories', 'fr_FR', '[categories]');

        $this->translate('categories', 'fr_FR');
    }
}
