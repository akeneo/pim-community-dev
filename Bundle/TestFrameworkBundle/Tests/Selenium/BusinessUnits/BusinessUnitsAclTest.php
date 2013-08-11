<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class BusinessUnitsAclTest extends \PHPUnit_Extensions_Selenium2TestCase
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
    public function testCreateBusinessUnit()
    {
        $unitname = 'Unit_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openBusinessUnits()
            ->add()
            ->assertTitle('Create Business Unit - Business Units - System')
            ->setBusinessUnitName($unitname)
            ->save()
            ->assertMessage('Business Unit successfully saved')
            ->assertTitle('Business Units - System')
            ->close();

        return $unitname;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateBusinessUnit
     * @param $username
     * @param $role
     * @param $unitname
     * @param string $aclcase
     * @dataProvider columnTitle
     */
    public function testBusinessUnitAcl($aclcase, $username, $role, $unitname)
    {
        $rolename = 'ROLE_NAME_' . $role;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        switch ($aclcase) {
            case 'delete':
                $this->deleteAcl($login, $rolename, $username, $unitname);
                break;
            case 'update':
                $this->updateAcl($login, $rolename, $username, $unitname);
                break;
            case 'create':
                $this->createAcl($login, $rolename, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $rolename, $unitname);
                break;
            case 'view list':
                $this->viewListAcl($login, $rolename, $username);
                break;
        }
    }

    public function deleteAcl($login, $rolename, $username, $unitname)
    {
        $login->openRoles()
            ->filterBy('Role', $rolename)
            ->open(array($rolename))
            ->selectAcl('Delete business unit')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openBusinessUnits()
            ->checkContextMenu($unitname, 'Delete');
    }

    public function updateAcl($login, $rolename, $username, $unitname)
    {
        $login->openRoles()
            ->filterBy('Role', $rolename)
            ->open(array($rolename))
            ->selectAcl('Edit business unit')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openBusinessUnits()
            ->checkContextMenu($unitname, 'Update');
    }

    public function createAcl($login, $rolename, $username)
    {
        $login->openRoles()
            ->filterBy('Role', $rolename)
            ->open(array($rolename))
            ->selectAcl('Create business unit')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openBusinessUnits()
            ->assertElementNotPresent("//div[@class = 'container-fluid']//a[contains(., 'Create business unit')]");
    }

    public function viewAcl($login, $username, $rolename, $unitname)
    {
        $login->openRoles()
            ->filterBy('Role', $rolename)
            ->open(array($rolename))
            ->selectAcl('View business unit')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openBusinessUnits()
            ->checkContextMenu($unitname, 'View');
    }

    public function viewListAcl($login, $rolename, $username)
    {
        $login->openRoles()
            ->filterBy('Role', $rolename)
            ->open(array($rolename))
            ->selectAcl('View business units list')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openBusinessUnits()
            ->assertTitle('403 - Forbidden');
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
            'view' => array('view'),
            'view list' => array('view list'),
        );
    }
}
