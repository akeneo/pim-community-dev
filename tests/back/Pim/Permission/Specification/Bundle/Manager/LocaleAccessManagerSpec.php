<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Manager;

use Akeneo\Pim\Permission\Bundle\Entity\LocaleAccess;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Persistence\ObjectManager;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\LocaleAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
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
            LocaleAccess::class
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
