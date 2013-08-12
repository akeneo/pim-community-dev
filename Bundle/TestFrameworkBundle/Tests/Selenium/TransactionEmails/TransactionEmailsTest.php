<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class TransactionEmailsTest extends \PHPUnit_Extensions_Selenium2TestCase
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
    public function testCreateTransactionEmail()
    {
        $email = 'Email'.mt_rand() . '@mail.com';

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTransactionEmails()
            ->add()
            ->assertTitle('Add Notification Rule - Transaction Emails - System')
            ->setEntityName('User')
            ->setEvent('Entity create')
            ->setTemplate('user')
            ->setUser('admin')
            ->setGroups(array('Marketing'))
            ->setEmail($email)
            ->save()
            ->assertMessage('Email notification rule has been saved')
            ->assertTitle('Transaction Emails - System')
            ->close();

        return $email;
    }

    /**
     * @depends testCreateTransactionEmail
     * @param $email
     * @return string
     */
    public function testUpdateTransactionEmail($email)
    {
        $newemail = 'Update_' . $email;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTransactionEmails()
            ->open(array($email))
            ->setEmail($newemail)
            ->save()
            ->assertMessage('Email notification rule has been saved')
            ->assertTitle('Transaction Emails - System')
            ->close();

        return $newemail;
    }

    /**
     * @depends testUpdateTransactionEmail
     * @param $email
     */
    public function testDeleteTransactionEmail($email)
    {
//        $this->markTestSkipped('BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTransactionEmails()
            ->delete('Recipient email', $email)
            ->assertTitle('Transaction Emails - System')
            ->assertMessage('Item was deleted');
    }
}
