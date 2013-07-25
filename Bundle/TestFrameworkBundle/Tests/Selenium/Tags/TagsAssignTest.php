<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class TagsAssignTest extends \PHPUnit_Extensions_Selenium2TestCase
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
    public function testCreateTag()
    {
        $tagname = 'Tag_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTags()
            ->add()
            ->assertTitle('Create tag')
            ->setTagname($tagname)
            ->save()
            ->assertMessage('Tag successfully saved')
            ->close()
            ->assertTitle('Tags');

        return $tagname;
    }

    /**
     * @depends testCreateTag
     * @param $tagname
     */
    public function testAccountTag($tagname)
    {
        $accountname = 'Account_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->add()
            ->setAccountName($accountname)
            ->verifyTag($tagname)
            ->setTag('New_' . $tagname)
            ->save()
            ->assertMessage('Account successfully saved')
            ->close()
            ->filterBy('Name', $accountname)
            ->open(array($accountname))
            ->verifyTag($tagname);
    }

    /**
     * @depends testCreateTag
     * @param $tagname
     */
    public function testContactTag($tagname)
    {
        $contactname = 'Contact_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openContacts()
            ->add()
            ->setFirstName($contactname . '_first')
            ->setLastName($contactname . '_last')
            ->setEmail($contactname . '@mail.com')
            ->verifyTag($tagname)
            ->setTag('New_' . $tagname)
            ->save()
            ->assertMessage('Contact successfully saved')
            ->close()
            ->filterBy('Email', $contactname . '@mail.com')
            ->open(array($contactname))
            ->verifyTag($tagname);
    }

    /**
     * @depends testCreateTag
     * @param $tagname
     */
    public function testUserTag($tagname)
    {
        $username = 'User_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers(false)
            ->add()
            ->setUsername($username)
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstname('First_'.$username)
            ->setLastname('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Manager'))
            ->verifyTag($tagname)
            ->setTag('New_' . $tagname)
            ->save()
            ->assertMessage('User successfully saved')
            ->close()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->verifyTag($tagname);
    }
}
