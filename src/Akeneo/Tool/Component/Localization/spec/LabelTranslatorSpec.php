<?php

namespace spec\Akeneo\Tool\Component\Localization;

use Symfony\Component\Translation\TranslatorBagInterface;
use Akeneo\Tool\Component\Localization\LabelTranslator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\MessageCatalogueInterface;

class LabelTranslatorSpec extends ObjectBehavior
{
    function let(TranslatorBagInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LabelTranslator::class);
    }

    function it_translates_labels_and_returns_fallback_if_not_found(TranslatorBagInterface $translator, MessageCatalogueInterface $catalogue)
    {
        $translator->getCatalogue('fr_FR')->willReturn($catalogue);
        $catalogue->defines('some.key')->willReturn(true);
        $catalogue->defines('not.found')->willReturn(false);
        $translator->trans('some.key', [], null, 'fr_FR')->willReturn('une traduction');

        $this->translate('some.key', 'fr_FR', '[fallback]')->shouldReturn('une traduction');
        $this->translate('not.found', 'fr_FR', '[fallback]')->shouldReturn('[fallback]');
    }
}
