<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class UsersTest extends Selenium2TestCase
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

     /**
     * @return string
     */
    public function testCreateUser()
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
            ->setRoles(array('Manager'))
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->assertTitle('Users - Users Management - System');

        return $username;
    }

    /**
     * @depends testCreateUser
     * @param $username
     * @return string
     */
    public function testUpdateUser($username)
    {
        $newUsername = 'Update_' . $username;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->edit()
            ->assertTitle('First_' . $username . ' Last_' . $username . ' - Edit - Users - Users Management - System')
            ->setUsername($newUsername)
            ->setFirstName('First_' . $newUsername)
            ->setLastName('Last_' . $newUsername)
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->assertTitle('Users - Users Management - System')
            ->close();

        return $newUsername;
    }

    /**
     * @depends testUpdateUser
     * @param $username
     */
    public function testHistoryWindow($username)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->checkHistoryWindow()
            ->close();
    }

    /**
     * @depends testUpdateUser
     * @param $username
     */
    public function testDeleteUser($username)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->delete()
            ->assertTitle('Users - Users Management - System')
            ->assertMessage('User deleted');

        $login->openUsers()
            ->filterBy('Username', $username)
            ->assertNoDataMessage('No user was found to match your search');
    }
}
