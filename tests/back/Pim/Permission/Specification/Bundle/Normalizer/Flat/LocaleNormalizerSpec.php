<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Normalizer\Flat;

use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $localeNormalizer, LocaleAccessManager $accessManager)
    {
        $this->beConstructedWith($localeNormalizer, $accessManager);
    }

    function it_normalize_a_locale_with_access_informations($localeNormalizer, $accessManager, LocaleInterface $en, Group $allGroup, Group $managerGroup, Group $adminGroup)
    {
        $localeNormalizer->normalize($en, 'csv', ['versioning' => true])->willReturn(['code' => 'en_US']);
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
