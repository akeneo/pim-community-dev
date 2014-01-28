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
    function let(
        LocaleRepository $repository,
        SecurityContextInterface $securityContext,
        SecurityFacade $securityFacade
    ) {
        $this->beConstructedWith($repository, $securityContext, $securityFacade, 'en_US');
    }

    function it_provides_locale_from_the_request_if_it_has_been_set(Request $request)
    {
        $request->getLocale()->willReturn('fr_FR');

        $this->setRequest($request);
        $this->getCurrentLocale()->shouldReturn('fr_FR');
    }

    function it_provides_default_locale_if_request_has_not_been_set()
    {
        $this->getCurrentLocale()->shouldReturn('en_US');
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

    function it_provides_active_locales_for_which_current_user_has_access(Locale $en, Locale $fr, $repository, $securityFacade)
    {
        $repository->getActivatedLocales()->willReturn([$en, $fr]);
        $fr->getCode()->willReturn('fr_FR');
        $en->getCode()->willReturn('en_US');
        $securityFacade->isGranted('pim_enrich_locale_fr_FR')->willReturn(true);
        $securityFacade->isGranted('pim_enrich_locale_en_US')->willReturn(false);

        $this->getUserLocales()->shouldReturn([$fr]);
    }

    function it_provides_active_locales_codes_for_which_current_user_has_access(
        Locale $en,
        Locale $fr,
        $repository,
        $securityFacade
    ) {
        $repository->getActivatedLocales()->willReturn([$en, $fr]);
        $fr->getCode()->willReturn('fr_FR');
        $en->getCode()->willReturn('en_US');
        $securityFacade->isGranted('pim_enrich_locale_fr_FR')->willReturn(true);
        $securityFacade->isGranted('pim_enrich_locale_en_US')->willReturn(false);

        $this->getUserCodes()->shouldReturn(['fr_FR']);
    }

    function it_provides_available_fallback_locales(Locale $qatar, Locale $belarus, $repository)
    {
        $repository->getAvailableFallbacks()->willReturn([$qatar, $belarus]);
        $qatar->getCode()->willReturn('ar_QA');
        $belarus->getCode()->willReturn('be_BY');

        $this->getFallbackCodes()->shouldReturn(['ar_QA', 'be_BY']);
    }

    function it_provides_data_locale_from_the_request_query(
        Request $request,
        Locale $fr,
        $repository,
        $securityFacade
    ) {
        $request->get('dataLocale')->willReturn('fr_FR');
        $repository->getActivatedLocales()->willReturn([$fr]);
        $fr->getCode()->willReturn('fr_FR');
        $securityFacade->isGranted('pim_enrich_locale_fr_FR')->willReturn(true);

        $this->setRequest($request);
        $this->getDataLocale()->shouldReturn($fr);
    }

    function its_getDataLocale_throws_exception_when_data_locale_is_not_activated_or_accessible(
        Request $request,
        $repository,
        $securityFacade
    ) {
        $request->get('dataLocale')->willReturn('fr_FR');
        $repository->getActivatedLocales()->willReturn([]);

        $this->setRequest($request);
        $this->shouldThrow(new \Exception('Data locale must be activated, and accessible through ACLs'))->duringGetDataLocale();
    }
}
