<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class LoginFormTest extends Selenium2TestCase
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

    protected function waitPageToLoad()
    {
        $this->waitUntil(
            function ($testCase) {
                $status = $testCase->execute(
                    array('script' => "return 'complete' == document['readyState']", 'args' => array())
                );
                if ($status) {
                    return true;
                } else {
                    return null;
                }
            },
            intval(MAX_EXECUTION_TIME)
        );

        $this->waitUntil(
            function ($testCase) {
                $status = $testCase->execute(
                    array('script' => "return !!document['page-rendered']", 'args' => array())
                );
                if ($status) {
                    return true;
                } else {
                    return null;
                }
            },
            intval(MAX_EXECUTION_TIME)
        );

        $this->timeouts()->implicitWait(intval(TIME_OUT));
    }

    protected function waitForAjax()
    {
        $this->waitUntil(
            function ($testCase) {
                $status = $testCase->execute(array('script' => 'return jQuery.active == 0', 'args' => array()));
                if ($status) {
                    return true;
                } else {
                    return null;
                }
            },
            intval(MAX_EXECUTION_TIME)
        );

        $this->timeouts()->implicitWait(intval(TIME_OUT));
    }

    public function testHasLoginForm()
    {
        $this->url('user/login');
        $this->waitPageToLoad();

        $username = $this->byId('prependedInput');
        $password = $this->byId('prependedInput2');

        //check that username and password is empty field
        $this->assertEquals('', $username->value());
        $this->assertEquals('', $password->value());
    }

    public function testLoginFormSubmitsToAdmin()
    {
        $this->url('user/login');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->byId('prependedInput')->clear();
        $this->byId('prependedInput')->value(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN);
        $this->byId('prependedInput2')->clear();
        $this->byId('prependedInput2')->value(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS);
        $this->clickOnElement('_submit');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertEquals('Dashboard', $this->title());

        $this->byXPath("//*[@id='top-page']//ul[@class='nav pull-right user-menu']/li[@class='dropdown']/a")->click();
        $this->byXPath("//*[@id='top-page']//ul[@class='nav pull-right user-menu']//li/a[contains(.,'Logout')]")->click();
        $this->assertEquals('Login', $this->title());
    }

    /**
     * @dataProvider createData
     * @param $login
     * @param $password
     */
    public function testLoginFormNotSubmitsToAdmin($login, $password)
    {
        $this->url('user/login');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->byId('prependedInput')->clear();
        $this->byId('prependedInput')->value($login);
        $this->byId('prependedInput2')->clear();
        $this->byId('prependedInput2')->value($password);
        $this->clickOnElement('_submit');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $actualResult = $this->byXPath("//div[contains(@class,'alert')]/div")->text();

        $this->assertEquals('Login', $this->title());
        $this->assertEquals("Bad credentials", $actualResult);
    }

    /**
     * @return array
     */
    public static function createData()
    {
        return array(
            array(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN, '12345'),
            array('12345', PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
        );
    }

    public function testLoginRequiredFiled()
    {
        $this->url('user/login');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $usernameAttribute = $this->byId('prependedInput')->attribute('required');
        $passwordAttribute = $this->byId('prependedInput2')->attribute('required');

        //check that username and password is empty field
        $this->assertEquals('true', $usernameAttribute);
        $this->assertEquals('true', $passwordAttribute);
    }

    public function testForgotPassword()
    {
        $this->url('user/login');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->byXPath("//*[@id='top-page']//fieldset//a[contains(.,'Forgot your password?')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertEquals('Forgot Password', $this->title());

        $this->byId('prependedInput')->value('123test123');
        $this->byXPath("//button[contains(.,'Request')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $messageActual = $this->byXPath("//*[@id='top-page']//div[contains(@class,'alert-error')]")->text();
        $messageExpect = "The username or email address \"123test123\" does not exist.";
        $this->assertEquals($messageExpect, $messageActual);

        $this->byId('prependedInput')->value('admin@example.com');
        $this->byXPath("//button[contains(.,'Request')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $messageActual = $this->byXPath("//*[@id='top-page']//div[contains(@class,'alert-success')]")->text();

        $this->assertEquals('An email has been sent to ...@example.com. It contains a link you must click to reset your password.', $messageActual);
    }

    public function testRememberFunction()
    {
        $this->url('user/login');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->byId('prependedInput')->clear();
        $this->byId('prependedInput')->value(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN);
        $this->byId('prependedInput2')->clear();
        $this->byId('prependedInput2')->value(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS);
        $this->byId('remember_me')->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->clickOnElement('_submit');
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertEquals('Dashboard', $this->title());

        $this->url(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertEquals('Dashboard', $this->title());
    }
}
