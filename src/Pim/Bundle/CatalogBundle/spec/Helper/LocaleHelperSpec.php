<?php

namespace spec\Pim\Bundle\CatalogBundle\Helper;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Intl;

class LocaleHelperSpec extends ObjectBehavior
{
    function let(UserContext $userContext, LocaleRepositoryInterface $localeRepository, LocaleInterface $en)
    {
        $en->getCode()->willReturn('en_US');
        $userContext->getCurrentLocale()->willReturn($en);

        $this->beConstructedWith($userContext, $localeRepository);
    }

    function it_provides_current_locale($en)
    {
        $this->getCurrentLocaleCode()->shouldReturn('en_US');
    }

    function it_provides_translated_locale_label()
    {
        $this->getLocaleLabel('en_US')->shouldReturn('English (United States)');
        $this->getLocaleLabel('en_US', 'fr_FR')->shouldReturn('anglais (États-Unis)');
    }

    function it_returns_the_original_label_if_a_translation_does_not_exist()
    {
        $this->getLocaleLabel('foo')->shouldReturn('foo');
    }

    function it_provides_a_currency_symbol_for_the_specified_currency_and_locale()
    {
        $this->getCurrencySymbol('USD')->shouldReturn('$');
        $this->getCurrencySymbol('USD', 'fr_FR')->shouldReturn('$US');
    }

    function it_provides_a_currency_label_for_the_specified_currency_and_locale()
    {
        $this->getCurrencyLabel('USD')->shouldReturn('US Dollar');
        $this->getCurrencyLabel('USD', 'fr_FR')->shouldReturn('dollar des États-Unis');
    }

    function it_provides_a_list_of_available_currency_labels_for_the_specified_locale()
    {
        $this->getCurrencyLabels()->shouldReturn(Intl\Intl::getCurrencyBundle()->getCurrencyNames('en'));
        $this->getCurrencyLabels('fr_FR')->shouldReturn(Intl\Intl::getCurrencyBundle()->getCurrencyNames('fr'));
    }

    function it_provides_translated_locales_as_choice($localeRepository)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $this->getActivatedLocaleChoices()->shouldReturn(
            [
                'fr_FR' => 'French (France)',
                'en_US' => 'English (United States)'
            ]
        );
    }
}
