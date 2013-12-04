<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Pages\Pages;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class TagsAcl extends Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected function setUp()
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(intval(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT));
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
    }

    protected function tearDown()
    {
        $this->cookie()->clear();
    }

    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles()
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setOwner('Main')
            ->setEntity('Tag', array('Create', 'Edit', 'Delete', 'View'), 'System')
            ->setEntity('User', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setEntity('Group', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setEntity('Role', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setCapability(
                array(
                    'Tag assign/unassign',
                    'Unassign all tags from entities',
                    'View tag cloud'),
                'System'
            )
            ->save()
            ->assertMessage('Role saved')
            ->close();

        return ($randomPrefix);
    }

    /**
     * @depends testCreateRole
     * @param $role
     * @return string
     */
    public function testCreateUser($role)
    {
        $username = 'User_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->add()
            ->assertTitle('Create User - Users - Users Management - System')
            ->setUsername($username)
            ->setOwner('Main')
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstName('First_'.$username)
            ->setLastName('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Label_' . $role))
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->assertTitle('Users - Users Management - System');

        return $username;
    }

    /**
     * @depends testCreateUser
     * @return string
     */
    public function testCreateTag()
    {
        $tagname = 'Tag_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTags()
            ->add()
            ->assertTitle('Create Tag - Tags - System')
            ->setTagname($tagname)
            ->setOwner('admin')
            ->save()
            ->assertMessage('Tag saved')
            ->assertTitle('Tags - System')
            ->close();

        return $tagname;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateTag
     * @param $username
     * @param $role
     * @param $tagname
     * @param string $aclcase
     * @dataProvider columnTitle
     */
    public function testTagAcl($aclcase, $username, $role, $tagname)
    {
        $rolename = 'Label_' .  $role;
            $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        switch ($aclcase) {
            case 'delete':
                $this->deleteAcl($login, $rolename, $username, $tagname);
                break;
            case 'update':
                $this->updateAcl($login, $rolename, $username, $tagname);
                break;
            case 'create':
                $this->createAcl($login, $rolename, $username);
                break;
            case 'view list':
                $this->viewListAcl($login, $rolename, $username);
                break;
            case 'unassign global':
                $this->unassignGlobalAcl($login, $rolename, $tagname);
                break;
            case 'assign unassign':
                $this->assignAcl($login, $rolename, $username);
                break;
        }
    }

    public function deleteAcl($login, $role, $username, $tagname)
    {
        $login->openRoles()
            ->filterBy('Label', $role)
            ->open(array($role))
            ->setEntity('Tag', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTags()
            ->checkContextMenu($tagname, 'Delete');
    }

    public function updateAcl($login, $role, $username, $tagname)
    {
        $login->openRoles()
            ->filterBy('Label', $role)
            ->open(array($role))
            ->setEntity('Tag', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTags()
            ->checkContextMenu($tagname, 'Update');
    }

    public function createAcl($login, $role, $username)
    {
        $login->openRoles()
            ->filterBy('Label', $role)
            ->open(array($role))
            ->setEntity('Tag', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTags()
            ->assertElementNotPresent("//div[@class = 'container-fluid']//a[contains(., 'Create tag')]");
    }

    public function viewListAcl($login, $role, $username)
    {
        $login->openRoles()
            ->filterBy('Label', $role)
            ->open(array($role))
            ->setEntity('Tag', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTags()
            ->assertTitle('403 - Forbidden');
    }

    public function unassignGlobalAcl($login, $rolename, $tagname)
    {
        $username = 'user' . mt_rand();
        $login->openRoles()
            ->filterBy('Label', $rolename)
            ->open(array($rolename))
            ->setCapability(array('Unassign all tags from entities'), 'None')
            ->save()
            ->openUsers()
            ->add()
            ->setUsername($username)
            ->enable()
            ->setOwner('Main')
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstName('First_'.$username)
            ->setLastName('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array($rolename))
            ->setTag($tagname)
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->edit()
            ->assertElementNotPresent(
                "//div[@id='s2id_oro_user_user_form_tags']//li[contains(., '{$tagname}')]" .
                "/a[@class='select2-search-choice-close']"
            );
    }

    public function assignAcl($login, $role, $username)
    {
        $login->openRoles()
            ->filterBy('Label', $role)
            ->open(array($role))
            ->setCapability(array('Tag assign/unassign'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openUsers()
            ->add()
            ->assertElementNotPresent(
                "//div[@class='select2-container select2-container-multi select2-container-disabled']"
            );
    }

    /**
     * Data provider for Tags ACL test
     *
     * @return array
     */
    public function columnTitle()
    {
        return array(
            'unassign global' => array('unassign global'),
            'assign unassign' => array('assign unassign'),
            'delete' => array('delete'),
            'update' => array('update'),
            'create' => array('create'),
            'view list' => array('view list'),
        );
    }
}
