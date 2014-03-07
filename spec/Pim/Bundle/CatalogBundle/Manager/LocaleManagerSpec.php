<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Entity\Locale;

class LocaleManagerSpec extends ObjectBehavior
{
    function let(LocaleRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_provides_active_locales(Locale $en, Locale $fr, $repository)
    {
        $repository->getActivatedLocales()->willReturn([$en, $fr]);

        $this->getActiveLocales()->shouldReturn([$en, $fr]);
    }

    function it_provides_deactivated_locales(Locale $de, $repository)
    {
        $repository->findBy(['activated' => false])->willReturn([$de]);

        $this->getDisabledLocales()->shouldReturn([$de]);
    }

    function it_provides_locales_by_criteria(Locale $fr, $repository)
    {
        $repository->findBy(['foo' => 'bar'])->willReturn([$fr]);

        $this->getLocales(['foo' => 'bar'])->shouldReturn([$fr]);
    }

    function it_provides_a_locale_by_its_code(Locale $pl, $repository)
    {
        $repository->findOneBy(['code' => 'pl'])->willReturn($pl);

        $this->getLocaleByCode('pl')->shouldReturn($pl);
    }

    function it_provides_active_locales_codes($repository, Locale $fr, Locale $be)
    {
        $fr->getCode()->willReturn('fr_FR');
        $be->getCode()->willReturn('fr_BE');
        $repository->getActivatedLocales()->willReturn([$fr, $be]);

        $this->getActiveCodes()->shouldReturn(['fr_FR', 'fr_BE']);
    }
}
