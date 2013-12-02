<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class TransactionEmailsAcl extends Selenium2TestCase
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
            ->setEntity('Email Notification', array('Create', 'Edit', 'Delete', 'View'), 'System')
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
            ->enable()
            ->setOwner('Main')
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
    public function testCreateTransactionEmail()
    {
        $email = 'Email'.mt_rand() . '@mail.com';

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTransactionEmails()
            ->add()
            ->assertTitle('Add Notification Rule - Notifications - Emails - System')
            ->setEmail($email)
            ->setEntityName('User')
            ->setEvent('Entity create')
            ->setTemplate('user')
            ->setUser('admin')
            ->setGroups(array('Marketing'))
            ->save()
            ->assertMessage('Email notification rule saved')
            ->assertTitle('Notifications - Emails - System')
            ->close();

        return $email;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateTransactionEmail
     * @param $username
     * @param $role
     * @param $email
     * @param string $aclcase
     * @dataProvider columnTitle
     */
    public function testTransactionEmailAcl($aclcase, $username, $role, $email)
    {
        $rolename = 'Label_' . $role;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        switch ($aclcase) {
            case 'delete':
                $this->deleteAcl($login, $rolename, $username, $email);
                break;
            case 'update':
                $this->updateAcl($login, $rolename, $username, $email);
                break;
            case 'create':
                $this->createAcl($login, $rolename, $username);
                break;
            case 'view':
                $this->viewListAcl($login, $rolename, $username);
                break;
        }
    }

    public function deleteAcl($login, $rolename, $username, $email)
    {
        $login->openRoles()
            ->filterBy('Label', $rolename)
            ->open(array($rolename))
            ->setEntity('Email Notification', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTransactionEmails()
            ->checkContextMenu($email, 'Delete');
    }

    public function updateAcl($login, $rolename, $username, $email)
    {
        $login->openRoles()
            ->filterBy('Label', $rolename)
            ->open(array($rolename))
            ->setEntity('Email Notification', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTransactionEmails()
            ->checkContextMenu($email, 'Update');
    }

    public function createAcl($login, $rolename, $username)
    {
        $login->openRoles()
            ->filterBy('Label', $rolename)
            ->open(array($rolename))
            ->setEntity('Email Notification', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTransactionEmails()
            ->assertElementNotPresent("//div[@class = 'container-fluid']//a[contains(., 'Create notification rule')]");
    }

    public function viewListAcl($login, $rolename, $username)
    {
        $login->openRoles()
            ->filterBy('Label', $rolename)
            ->open(array($rolename))
            ->setEntity('Email Notification', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openTransactionEmails()
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
            'view list' => array('view'),
        );
    }
}
