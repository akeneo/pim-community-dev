<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Users;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class AdvancedSearchTest extends Selenium2TestCase
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
     * Tests that checks advanced search
     *
     * @dataProvider columnTitle
     */
    public function testAdvancedSearch($query, $userField)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers();
        $users = new Users($this);
        $userData = $users->getRandomEntity();
        $login->openNavigation(array('url' => '/search/advanced-search-page'));
        //Fill advanced search input field
        $login->byId('query')->value($query . $userData[$userField]);
        $login->byId('sendButton')->click();
        $login->waitPageToLoad();
        $login->waitForAjax();
        //Check that result is not null
        $result = strtolower($userData['USERNAME']);
        $login->assertElementPresent(
            "//div[@class='container-fluid']//div[@class='search_stats alert alert-info']//h3[contains(., '{$result}')]",
            'Search results does not found'
        );
    }

    /**
     * Data provider for advanced search
     *
     * @return array
     */
    public function columnTitle()
    {
        return array(
            'firstName' => array('where firstName ~ ','FIRST NAME'),
        );
    }
}
