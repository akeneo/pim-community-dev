<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
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
            new \InvalidArgumentException(
                'Expects a "PimEnterprise\Component\Security\Updater\LocaleAccessUpdater", "stdClass" provided.'
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_an_attribute_group(
        $groupRepository,
        $localeRepository,
        LocaleAccessInterface $localeAccess,
        Group $userGroup,
        LocaleInterface $locale
    ) {
        $values = [
            'locale'        => 'en_US',
            'userGroup'     => 'IT Manager',
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

        $this->shouldThrow(new \InvalidArgumentException('Group with "foo" code does not exist'))
            ->during('update', [$localeAccess, ['userGroup' => 'foo']]);
    }

    function it_throws_an_exception_if_locale_not_found(
        $localeRepository,
        LocaleAccessInterface $localeAccess
    ) {
        $localeRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException('Locale with "foo" code does not exist'))
            ->during('update', [$localeAccess, ['locale' => 'foo']]);
    }
}
