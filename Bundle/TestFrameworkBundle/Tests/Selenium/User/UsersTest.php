<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class UsersTest extends \PHPUnit_Extensions_Selenium2TestCase
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
            ->openNavigation()
            ->tab('System')
            ->menu('Users')
            ->openUsers(false)
            ->add()
            ->assertTitle('Create User - Users - System')
            ->setUsername($username)
            ->enable()
            ->setOwner('Default')
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstname('First_'.$username)
            ->setLastname('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Manager'))
            ->save()
            ->assertMessage('User successfully saved')
            ->close()
            ->assertTitle('Users - System');

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
            ->openNavigation()
            ->tab('System')
            ->menu('Users')
            ->openUsers(false)
            ->filterBy('Username', $username)
            ->open(array($username))
            ->edit()
            ->assertTitle('Last_' . $username . ', First_' . $username . ' - Users - System')
            ->setUsername($newUsername)
            ->setFirstname('First_' . $newUsername)
            ->setLastname('Last_' . $newUsername)
            ->save()
            ->assertTitle('Users - System')
            ->assertMessage('User successfully saved')
            ->close();

        return $newUsername;
    }

    /**
     * @depends testUpdateUser
     * @param $username
     */
    public function testDeleteUser($username)
    {
        $this->markTestSkipped('BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->delete()
            ->assertTitle('Users - System')
            ->assertMessage('Item was deleted');

        $login->openUsers()->filterBy('Username', $username)->assertNoDataMessage('No users were found to match your search');
    }
}
