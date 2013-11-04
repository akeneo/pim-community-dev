<?php

namespace Oro\Bundle\TestFrameworkBundle\Test;

abstract class Selenium2TestCase extends \PHPUnit_Extensions_Selenium2TestCase
{
    public function prepareSession()
    {
        $res = parent::prepareSession();
        if (defined('PHPUNIT_SELENIUM_COVERAGE')) {
            $res->cookie()->remove('PHPUNIT_SELENIUM_TEST_ID');
            $this->url('/');
        }
        return $res;
    }
}
