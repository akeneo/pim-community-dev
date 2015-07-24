<?php

namespace spec\PimEnterprise\Bundle\TransformBundle\Normalizer\Flat;

use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function let(LocaleAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_normalize_a_locale_with_access_informations($accessManager, LocaleInterface $en, Group $allGroup, Group $managerGroup, Group $adminGroup)
    {
        $en->getCode()->willReturn('en_US');
        $accessManager->getViewUserGroups($en)->willReturn([$allGroup]);
        $allGroup->__toString()->willReturn('All');
        $accessManager->getEditUserGroups($en)->willReturn([$managerGroup]);
        $managerGroup->__toString()->willReturn('Manager');

        $this->normalize($en, 'csv', ['versioning' => true])->shouldReturn([
            'code'            => 'en_US',
            'view_permission' => 'All',
            'edit_permission' => 'Manager'
        ]);
    }
}
