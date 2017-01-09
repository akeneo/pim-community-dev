<?php

namespace spec\PimEnterprise\Component\Security\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Component\Security\Model\LocaleAccessInterface;

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
        $this->shouldHaveType('PimEnterprise\Component\Security\Updater\LocaleAccessUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'PimEnterprise\Component\Security\Model\LocaleAccessInterface'
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
                'updater',
                'locale access',
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
                'updater',
                'locale access',
                'foo'
            )
        )->during('update', [$localeAccess, ['locale' => 'foo']]);
    }
}
