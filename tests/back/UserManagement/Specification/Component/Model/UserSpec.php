<?php

namespace Specification\Akeneo\UserManagement\Component\Model;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Category\Infrastructure\Component\Classification\Model\Category;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    function it_has_properties()
    {
        $this->addProperty('propertyName', 'value')->shouldReturn(null);
        $this->getProperty('propertyName')->shouldReturn('value');
        $this->getProperty('property_name')->shouldReturn('value');
    }

    function it_initializes_the_user_as_a_non_app()
    {
        $this->isApiUser()->shouldReturn(false);
    }

    function it_defines_the_user_as_a_user_app()
    {
        $this->defineAsApiUser();
        $this->isApiUser()->shouldReturn(true);
    }

    function it_provides_a_profile()
    {
        $this->getProfile()->shouldReturn(null);
        $this->setProfile('manager');
        $this->getProfile()->shouldReturn('manager');
    }

    function it_set_an_empty_profile_as_null()
    {
        $this->getProfile()->shouldReturn(null);
        $this->setProfile('');
        $this->getProfile()->shouldReturn(null);
    }

    function it_can_be_duplicated()
    {
        $duplicated = $this->duplicate();
        $duplicated->getUsername()->shouldBeNull();
        $duplicated->isEnabled()->shouldBe(true);

        $role = new Role('ROLE_USER');
        $group = new Group();
        $this->setId(10);
        $this->setUsername('test username');
        $this->setEnabled(false);
        $this->setFirstName('first name');
        $this->setLastName('last name');
        $this->setMiddleName('middle name');
        $this->setNamePrefix('prefix');
        $this->setNameSuffix('suffix');
        $this->addRole($role);
        $this->addGroup($group);
        $this->defineAsApiUser();
        $this->setTimezone('+01:00');
        $uiLocale = new Locale();
        $uiLocale->setCode('en_US');
        $this->setUiLocale($uiLocale);
        $catalogLocale = new Locale();
        $catalogLocale->setCode('fr_FR');
        $this->setCatalogLocale($catalogLocale);
        $catalogScope = new Channel();
        $catalogScope->setCode('mobile');
        $this->setCatalogScope($catalogScope);
        $tree = new Category();
        $tree->setCode('master');
        $this->setDefaultTree($tree);
        $this->setPhone('+336512');
        $this->setEmailNotifications(true);
        $this->addProperty('name1', 'value1');
        $this->addProperty('name2', 'value2');
        $publicView = new DatagridView();
        $publicView->setLabel('1');
        $publicView->setType(DatagridView::TYPE_PUBLIC);
        $publicView->setDatagridAlias('alias1');
        $privateView = new DatagridView();
        $privateView->setLabel('1');
        $privateView->setType(DatagridView::TYPE_PRIVATE);
        $privateView->setDatagridAlias('alias2');
        $this->setDefaultGridView('alias1', $publicView);
        $this->setDefaultGridView('alias2', $privateView);
        $this->setPassword('encrypted');
        $this->setPlainPassword('password');
        $this->setProductGridFilters(['name', 'label']);

        $duplicated = $this->duplicate();
        $duplicated->getId()->shouldBeNull();
        $duplicated->getUsername()->shouldBeNull();
        $duplicated->isEnabled()->shouldBe(false);
        $duplicated->getFirstName()->shouldBeNull();
        $duplicated->getLastName()->shouldBeNull();
        $duplicated->getMiddleName()->shouldBeNull();
        $duplicated->getNamePrefix()->shouldBeNull();
        $duplicated->getNameSuffix()->shouldBeNull();
        $duplicated->getRoles()->shouldBe(['ROLE_USER']);
        $duplicated->getRolesCollection()->getValues()->shouldBe([$role]);
        $duplicated->getGroups()->shouldBeAnInstanceOf(ArrayCollection::class);
        $duplicated->getGroups()->toArray()->shouldBe([$group]);
        $duplicated->isApiUser()->shouldBe(true);
        $duplicated->getTimezone()->shouldBe('+01:00');
        $duplicated->getUiLocale()->shouldBe($uiLocale);
        $duplicated->getCatalogLocale()->shouldBe($catalogLocale);
        $duplicated->getCatalogScope()->shouldBe($catalogScope);
        $duplicated->getDefaultTree()->shouldBe($tree);
        $duplicated->getPhone()->shouldBe('+336512');
        $duplicated->isEmailNotifications()->shouldBe(true);
        $duplicated->getProperty('name1')->shouldBe('value1');
        $duplicated->getProperty('name2')->shouldBe('value2');
        $duplicated->getProperty('unknown')->shouldBe(null);
        $duplicated->getDefaultGridView('alias1')->shouldBe($publicView);
        $duplicated->getDefaultGridView('alias2')->shouldBe(null);
        $duplicated->getPassword()->shouldBe(null);
        $duplicated->getPlainPassword()->shouldBe(null);
        $duplicated->getLastLogin()->shouldBe(null);
        $duplicated->getLoginCount()->shouldBe(0);
        $duplicated->getProductGridFilters()->shouldBe(['name', 'label']);
    }

    function it_trims_fullname()
    {
        $this->setFirstName('Mary');
        $this->setLastName('Smith');
        $this->getFullName()->shouldEqual('Mary Smith');
    }

    function it_is_not_equal_if_not_same_class()
    {
        $this->isEqualTo(new InMemoryUser('user','password'))->shouldEqual(false);
    }

    function it_is_equal_if_duplicated()
    {
        $this->setRoles([new Role('role')]);
        $duplicate = $this->duplicate();
        $duplicate->setRoles([new Role('role')]);
        $this->isEqualTo($duplicate);
    }

    function it_is_not_equal_if_not_same_password()
    {
        $this->setPassword('p1');
        $duplicate = $this->duplicate();
        $duplicate->setPassword('p2');
        $this->isEqualTo($duplicate)->shouldEqual(false);
    }

    function it_is_not_equal_if_not_same_salt()
    {
        $this->setSalt('s1');
        $duplicate = $this->duplicate();
        $duplicate->setSalt('s2');
        $this->isEqualTo($duplicate)->shouldEqual(false);
    }

    function it_is_not_equal_if_changed_identifier()
    {
        $this->setUserName('i1');
        $duplicate = $this->duplicate();
        $duplicate->setUserName('i2');
        $this->isEqualTo($duplicate)->shouldEqual(false);
    }

    function it_is_not_equal_if_account_locked()
    {
        $this->setEnabled(true);
        $duplicate = $this->duplicate();
        $duplicate->setEnabled(false);
        $this->isEqualTo($duplicate)->shouldEqual(false);
    }

    function it_is_not_equal_if_account_disabled()
    {
        $this->setEnabled(true);
        $duplicate = $this->duplicate();
        $duplicate->setEnabled(false);
        $this->isEqualTo($duplicate)->shouldEqual(false);
    }
}
