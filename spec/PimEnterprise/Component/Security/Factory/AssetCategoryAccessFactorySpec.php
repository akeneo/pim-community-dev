<?php

namespace spec\PimEnterprise\Component\Security\Factory;

use PhpSpec\ObjectBehavior;

class AssetCategoryAccessFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess');
    }

    function it_creates_a_locale_access()
    {
        $this->create()->shouldReturnAnInstanceOf('PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess');
    }
}
