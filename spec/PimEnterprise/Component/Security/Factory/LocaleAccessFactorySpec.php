<?php

namespace spec\PimEnterprise\Component\Security\Factory;

use PhpSpec\ObjectBehavior;

class LocaleAccessFactorySpec extends ObjectBehavior
{
    const LOCALE_ACCESS_CLASS = 'PimEnterprise\Bundle\SecurityBundle\Entity\LocaleAccess';

    function let()
    {
        $this->beConstructedWith(self::LOCALE_ACCESS_CLASS);
    }

    function it_creates_a_locale_access()
    {
        $this->create()->shouldReturnAnInstanceOf(self::LOCALE_ACCESS_CLASS);
    }
}
