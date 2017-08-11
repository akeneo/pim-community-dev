<?php

namespace spec\Pim\Bundle\CatalogBundle\Helper;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Intl;

class LocaleHelperSpec extends ObjectBehavior
{
    function let(UserContext $userContext, LocaleRepositoryInterface $localeRepository, LocaleInterface $en)
    {
        $en->getCode()->willReturn('en_US');
        $userContext->getCurrentLocale()->willReturn($en);

        $this->beConstructedWith($userContext, $localeRepository);
    }

    function it_provides_translated_locale_label()
    {
        $this->getLocaleLabel('en_US')->shouldReturn('English (United States)');
        $this->getLocaleLabel('en_US', 'fr_FR')->shouldReturn('anglais (États-Unis)');
    }
}
