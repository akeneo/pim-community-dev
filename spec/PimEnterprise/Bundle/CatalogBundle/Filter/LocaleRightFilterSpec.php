<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class LocaleRightFilterSpec extends ObjectBehavior
{
    public function let(SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($securityContext);
    }

    public function it_does_not_filter_a_locale_if_the_user_is_granted_to_see_this_locale($securityContext, LocaleInterface $enUS)
    {
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $enUS)->willReturn(true);

        $this->filterObject($enUS, 'pim:locale:view', [])->shouldReturn(false);
    }

    public function it_filters_a_locale_if_the_user_is_not_granted_to_see_this_locale($securityContext, LocaleInterface $enUS)
    {
        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $enUS)->willReturn(false);

        $this->filterObject($enUS, 'pim:locale:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_locale(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:locale:view', ['channels' => ['en_US']]]);
    }
}
