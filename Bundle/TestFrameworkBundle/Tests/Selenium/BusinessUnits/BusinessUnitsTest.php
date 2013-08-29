<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class BusinessUnitsTest extends \PHPUnit_Extensions_Selenium2TestCase
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
            ->setOwner('Main')
            ->save()
            ->assertMessage('Business Unit successfully saved')
            ->assertTitle('Business Units - System')
            ->close();

        return $unitname;
    }

    /**
     * @depends testCreateBusinessUnit
     * @param $unitname
     * @return string
     */
    public function testUpdateBusinessUnit($unitname)
    {
        $newunitname = 'Update_' . $unitname;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openBusinessUnits()
            ->filterBy('Name', $unitname)
            ->open(array($unitname))
            ->edit()
            ->setBusinessUnitName($newunitname)
            ->save()
            ->assertTitle('Business Units - System')
            ->assertMessage('Business Unit successfully saved');

        return $newunitname;
    }

    /**
     * @depends testUpdateBusinessUnit
     * @param $unitname
     */
    public function testDeleteBusinessUnit($unitname)
    {
        $this->markTestSkipped('BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openBusinessUnits()
            ->filterBy('Name', $unitname)
            ->open(array($unitname))
            ->delete()
            ->assertTitle('Business Units - System')
            ->assertMessage('Item was deleted');
    }
}
