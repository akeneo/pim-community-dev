<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Translator;

class UiLocaleProviderSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $localeRepository,
        Translator $translator,
        MessageCatalogueInterface $messageCatalogueAll,
        MessageCatalogueInterface $messageCatalogue_fr_FR,
        MessageCatalogueInterface $messageCatalogue_en_US,
        MessageCatalogueInterface $messageCatalogue_de_DE
    ) {
        $this->beConstructedWith($translator, $localeRepository, 0.7, ['en_US', 'fr_FR', 'de_DE']);
        $translator->getFallbackLocales()->willReturn(['en_US']);
        $translator->getCatalogue(Argument::any())->willReturn($messageCatalogueAll);
        $messageCatalogueAll->all()->willReturn([]);

        $messageCatalogue_en_US->all()->willReturn([
            'scope1' => ['k1' => 't1', 'k2' => 't2', 'k3' => 't3'],
            'scope2' => ['k3' => 't3']
        ]);
        $translator->getCatalogue('en_US')->willReturn($messageCatalogue_en_US);

        $messageCatalogue_fr_FR->all()->willReturn([
            'scope1' => ['k1' => 't1', 'k2' => 't2'],
            'scope2' => ['k3' => 't3' ]
        ]);
        $translator->getCatalogue('fr_FR')->willReturn($messageCatalogue_fr_FR);

        $messageCatalogue_de_DE->all()->willReturn([
            'scope1' => ['k1' => 't1'],
            'scope2' => ['k3' => 'k3']
        ]);
        $translator->getCatalogue('de_DE')->willReturn($messageCatalogue_de_DE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UiLocaleProvider::class);
    }

    function it_should_return_default_locale_and_locales_translated_more_than_70_percent($localeRepository, LocaleInterface $enUs, LocaleInterface $frFr)
    {
        $localeRepository->findBy(['code' => ['en_US', 'fr_FR']])->willReturn([$enUs, $frFr]);
        $this->getLocales()->shouldReturn([$enUs, $frFr]);
    }
}
