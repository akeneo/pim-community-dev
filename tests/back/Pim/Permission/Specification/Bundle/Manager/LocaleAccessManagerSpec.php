<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Manager;

use Akeneo\Pim\Permission\Bundle\Entity\LocaleAccess;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\LocaleAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Prophecy\Argument;

class LocaleAccessManagerSpec extends ObjectBehavior
{
    function let(
        LocaleAccessRepository $repository,
        BulkSaverInterface $saver,
        BulkRemoverInterface $remover
    ) {
        $this->beConstructedWith(
            $repository,
            $saver,
            LocaleAccess::class,
            $remover
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

    function it_revokes_existing_locale_access_for_the_provided_user_group(
        LocaleAccessRepository $repository,
        BulkRemoverInterface $remover,
        LocaleInterface $locale,
        GroupInterface $group,
        LocaleAccess $access
    ) {
        $repository->findOneBy(['locale' => $locale, 'userGroup' => $group])->willReturn($access);

        $remover->removeAll([$access])->shouldBeCalled();

        $this->revokeGroupAccess($locale, $group);
    }

    function it_does_nothing_when_it_revokes_non_existing_locale_access_for_the_provided_user_group(
        LocaleAccessRepository $repository,
        BulkRemoverInterface $remover,
        LocaleInterface $locale,
        GroupInterface $group
    ) {
        $repository->findOneBy(['locale' => $locale, 'userGroup' => $group])->willReturn(null);

        $remover->removeAll(Argument::any())->shouldNotBeCalled();

        $this->revokeGroupAccess($locale, $group);
    }
}
