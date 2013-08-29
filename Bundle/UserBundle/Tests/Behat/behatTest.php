<?php

namespace Oro\Bundle\UserBundle\Tests\Behat;

use Oro\Bundle\TestFrameworkBundle\Test\BehatTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;

/**
 * @db_isolation
 */
class BehatTest extends BehatTestCase
{
    public function setUp()
    {
        static::createClient();
    }

    public function testCreateUserAcceptanceCriteria()
    {
        $this->runFeature(__DIR__);
    }

    /**
     * @group selenium
     */
    public function testCreateUserAcceptanceCriteriaSelenium()
    {
        $this->runFeature(__DIR__, null, array(), 'behat.selenium.yml');
    }
}
