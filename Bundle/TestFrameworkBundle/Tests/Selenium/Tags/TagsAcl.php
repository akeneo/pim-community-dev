<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Pages\Pages;

class TagsAcl extends \PHPUnit_Extensions_Selenium2TestCase
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
            ->setName('ROLE_NAME_' . $randomPrefix)
            ->setLabel('Label_' . $randomPrefix)
            ->selectAcl('Root')
            ->save()
            ->assertMessage('Role successfully saved')
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
            ->assertTitle('Create User - Users - System')
            ->setUsername($username)
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstname('First_'.$username)
            ->setLastname('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Label_' . $role))
            ->save()
            ->assertMessage('User successfully saved')
            ->close()
            ->assertTitle('Users - System');

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
            ->save()
            ->assertMessage('Tag successfully saved')
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
        $role = 'ROLE_NAME_' . $role;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        switch ($aclcase) {
            case 'delete':
                $this->deleteAcl($login, $role, $username, $tagname);
                break;
            case 'update':
                $this->updateAcl($login, $role, $username, $tagname);
                break;
            case 'create':
                $this->createAcl($login, $role, $username);
                break;
            case 'view list':
                $this->viewListAcl($login, $role, $username);
                break;
            case 'unassign global':
                $this->unassignGlobalAcl($login, $role, $tagname);
                break;
            case 'assign/unassign':
                $this->assignAcl($login, $role, $username);
                break;
        }
    }

    public function deleteAcl($login, $role, $username, $tagname)
    {
        $login->openRoles()
            ->filterBy('Role', $role)
            ->open(array($role))
            ->selectAcl('Delete tags')
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
            ->filterBy('Role', $role)
            ->open(array($role))
            ->selectAcl('Update tag')
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
            ->filterBy('Role', $role)
            ->open(array($role))
            ->selectAcl('Create tag')
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
            ->filterBy('Role', $role)
            ->open(array($role))
            ->selectAcl('View list of tags')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTags()
            ->assertTitle('403 - Forbidden');
    }

    public function unassignGlobalAcl($login, $role, $tagname)
    {
        $username = 'user' . mt_rand();
        $login->openRoles()
            ->filterBy('Role', $role)
            ->open(array($role))
            ->selectAcl('Tag unassign global')
            ->save()
            ->openUsers()
            ->add()
            ->setUsername($username)
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstname('First_'.$username)
            ->setLastname('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array($role))
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
                "//div[@id='s2id_oro_user_user_form_tags']//li[contains(., '{$tagname}')]/a[@class='select2-search-choice-close']"
            );
    }

    public function assignAcl($login, $role, $username)
    {
        $login->openRoles()
            ->filterBy('Role', $role)
            ->open(array($role))
            ->selectAcl('Tag assign/unassign')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts()
            ->add(false)
            ->assertElementPresent("//div[@class='select2-container select2-container-multi select2-container-disabled']");
    }

    /**
     * Data provider for Tags ACL test
     *
     * @return array
     */
    public function columnTitle()
    {
        return array(
            'delete' => array('delete'),
            'update' => array('update'),
            'create' => array('create'),
            'view list' => array('view list'),
            'unassign global' => array('unassign global'),
            'assign/unassign' => array('assign/unassign'),
        );
    }
}
