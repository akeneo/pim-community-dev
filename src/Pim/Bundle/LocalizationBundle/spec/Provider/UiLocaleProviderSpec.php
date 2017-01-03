<?php

namespace spec\Pim\Bundle\LocalizationBundle\Provider;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Translator;

class UiLocaleProviderSpec extends ObjectBehavior
{
    function let(
        Translator $translator,
        MessageCatalogueInterface $messageCatalogueAll,
        MessageCatalogueInterface $messageCatalogueFR,
        MessageCatalogueInterface $messageCatalogueEN,
        MessageCatalogueInterface $messageCatalogueDE
    ) {
        $this->beConstructedWith($translator, 0.7);
        $translator->getFallbackLocales()->willReturn(['en_US']);
        $translator->getCatalogue(Argument::any())->willReturn($messageCatalogueAll);
        $messageCatalogueAll->all()->willReturn([]);

        $messageCatalogueEN->all()->willReturn([
            'scope1' => ['k1' => 't1', 'k2' => 't2', 'k3' => 't3'],
            'scope2' => ['k3' => 't3']
        ]);
        $translator->getCatalogue('en')->willReturn($messageCatalogueEN);

        $messageCatalogueFR->all()->willReturn([
            'scope1' => ['k1' => 't1', 'k2' => 't2'],
            'scope2' => ['k3' => 't3' ]
        ]);
        $translator->getCatalogue('fr')->willReturn($messageCatalogueFR);

        $messageCatalogueDE->all()->willReturn([
            'scope1' => ['k1' => 't1'],
            'scope2' => ['k3' => 'k3']
        ]);
        $translator->getCatalogue('de')->willReturn($messageCatalogueDE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\LocalizationBundle\Provider\UiLocaleProvider');
    }

    function it_should_return_default_locale()
    {
        $this->getLocales()->shouldHaveKey('en');
    }

    function it_should_return_locales_translated_more_than_70_percent()
    {
        $this->getLocales()->shouldHaveKey('fr');
    }

    function it_should_not_return_locales_translated_less_than_70_percent()
    {
        $this->getLocales()->shouldNotHaveKey('de');
    }
}
