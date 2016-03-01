<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository;
use Prophecy\Argument;

class LocaleAccessManagerSpec extends ObjectBehavior
{
    function let(
        LocaleAccessRepository $repository,
        BulkSaverInterface $saver
    ) {
        $this->beConstructedWith(
            $repository,
            $saver,
            'PimEnterprise\Bundle\SecurityBundle\Entity\LocaleAccess'
        );
    }

    function it_provides_user_groups_that_have_access_to_a_locale(LocaleInterface $locale, $repository)
    {
        $repository->getGrantedUserGroups($locale, Attributes::VIEW_ITEMS)->willReturn(['foo', 'baz']);
        $repository->getGrantedUserGroups($locale, Attributes::EDIT_ITEMS)->willReturn(['baz']);

        $this->getViewUserGroups($locale)->shouldReturn(['foo', 'baz']);
        $this->getEditUserGroups($locale)->shouldReturn(['baz']);
    }

    function it_grants_access_on_an_locale_for_the_provided_user_groups(
        LocaleInterface $locale,
        Group $manager,
        Group $redactor,
        $repository,
        $saver
    ) {
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($locale, [$redactor, $manager])->shouldBeCalled();
        $saver->saveAll(Argument::size(2))->shouldBeCalled();

        $this->setAccess($locale, [$manager, $redactor], [$redactor]);
    }
}
