<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class EmailTemplateTest extends Selenium2TestCase
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
    public function testCreateEmailTemplate()
    {
        $templatename = 'EmailTemplate_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openEmailTemplates()
            ->add()
            ->assertTitle('Create Email Template - Templates - Emails - System')
            ->setEntityName('User')
            ->setType('Html')
            ->setName($templatename)
            ->setSubject('Subject')
            ->setContent('Template content')
            ->save()
            ->assertMessage('Template saved')
            ->assertTitle('Templates - Emails - System')
            ->close();

        return $templatename;
    }

    /**
     * @depends testCreateEmailTemplate
     * @param $templatename
     * @return string
     */
    public function testCloneEmailTemplate($templatename)
    {
        $newtemplatename = 'Clone_' . $templatename;
        $fields = array();
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openEmailTemplates()
            ->cloneEntity('Template name', $templatename)
            ->setName($newtemplatename)
            ->save()
            ->assertMessage('Template saved')
            ->assertTitle('Templates - Emails - System')
            ->close()
            ->open(array($newtemplatename))
            ->getFields($fields);
        $this->assertEquals('User', $fields['entityname']);
        $this->assertEquals('Html', $fields['type']);
        $this->assertEquals('Subject', $fields['subject']);
        $this->assertEquals('Template content', $fields['content']);

        return $newtemplatename;
    }

    /**
     * @depends testCreateEmailTemplate
     * @param $templatename
     * @return string
     */
    public function testUpdateEmailTemplate($templatename)
    {
        $newtemplatename = 'Update_' . $templatename;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openEmailTemplates()
            ->open(array($templatename))
            ->setName($newtemplatename)
            ->save()
            ->assertMessage('Template saved')
            ->assertTitle('Templates - Emails - System')
            ->close();

        return $newtemplatename;
    }

    /**
     * @depends testUpdateEmailTemplate
     * @param $templatename
     */
    public function testDeleteEmailTemplate($templatename)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openEmailTemplates()
            ->delete('Template name', $templatename)
            ->assertTitle('Templates - Emails - System')
            ->assertMessage('Item deleted');
    }
}
