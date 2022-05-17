<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Pim\Permission\Component\Updater\LocaleAccessUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\Pim\Permission\Component\Model\LocaleAccessInterface;

class LocaleAccessUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($groupRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleAccessUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                \stdClass::class,
                LocaleAccessInterface::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_an_attribute_group(
        $groupRepository,
        $localeRepository,
        LocaleAccessInterface $localeAccess,
        GroupInterface $userGroup,
        LocaleInterface $locale
    ) {
        $values = [
            'locale'        => 'en_US',
            'user_group'    => 'IT Manager',
            'view_products' => true,
            'edit_products' => false,
        ];

        $localeAccess->setLocale($locale)->shouldBeCalled();
        $localeAccess->setUserGroup($userGroup)->shouldBeCalled();
        $localeAccess->setViewProducts(true)->shouldBeCalled();
        $localeAccess->setEditProducts(false)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);

        $this->update($localeAccess, $values, []);
    }

    function it_updates_an_attribute_group_with_only_edit_permission(
        $groupRepository,
        $localeRepository,
        LocaleAccessInterface $localeAccess,
        GroupInterface $userGroup,
        LocaleInterface $locale
    ) {
        $values = [
            'locale'        => 'en_US',
            'user_group'    => 'IT Manager',
            'view_products' => false,
            'edit_products' => true,
        ];

        $localeAccess->setLocale($locale)->shouldBeCalled();
        $localeAccess->setUserGroup($userGroup)->shouldBeCalled();
        $localeAccess->setViewProducts(false)->shouldBeCalled();
        $localeAccess->setViewProducts(true)->shouldBeCalled();
        $localeAccess->setEditProducts(true)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);

        $this->update($localeAccess, $values, []);
    }

    function it_throws_an_exception_if_group_not_found(
        $groupRepository,
        LocaleAccessInterface $localeAccess
    ) {
        $groupRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'user_group',
                'group code',
                'The group does not exist',
                LocaleAccessUpdater::class,
                'foo'
            )
        )->during('update', [$localeAccess, ['user_group' => 'foo']]);
    }

    function it_throws_an_exception_if_locale_not_found(
        $localeRepository,
        LocaleAccessInterface $localeAccess
    ) {
        $localeRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'locale',
                'locale code',
                'The locale does not exist',
                LocaleAccessUpdater::class,
                'foo'
            )
        )->during('update', [$localeAccess, ['locale' => 'foo']]);
    }
}
