<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

class LocaleManagerSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_provides_active_locales(LocaleInterface $en, LocaleInterface $fr, $repository)
    {
        $repository->getActivatedLocales()->willReturn([$en, $fr]);

        $this->getActiveLocales()->shouldReturn([$en, $fr]);
    }

    function it_provides_deactivated_locales(LocaleInterface $de, $repository)
    {
        $repository->findBy(['activated' => false])->willReturn([$de]);

        $this->getDisabledLocales()->shouldReturn([$de]);
    }

    function it_provides_locales_by_criteria(LocaleInterface $fr, $repository)
    {
        $repository->findBy(['foo' => 'bar'])->willReturn([$fr]);

        $this->getLocales(['foo' => 'bar'])->shouldReturn([$fr]);
    }

    function it_provides_a_locale_by_its_code(LocaleInterface $pl, $repository)
    {
        $repository->findOneByIdentifier('pl')->willReturn($pl);

        $this->getLocaleByCode('pl')->shouldReturn($pl);
    }

    function it_provides_active_locales_codes($repository, LocaleInterface $fr, LocaleInterface $be)
    {
        $fr->getCode()->willReturn('fr_FR');
        $be->getCode()->willReturn('fr_BE');
        $repository->getActivatedLocales()->willReturn([$fr, $be]);

        $this->getActiveCodes()->shouldReturn(['fr_FR', 'fr_BE']);
    }
}
