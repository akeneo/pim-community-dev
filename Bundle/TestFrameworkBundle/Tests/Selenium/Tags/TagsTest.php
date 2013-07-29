<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class TagsTest extends \PHPUnit_Extensions_Selenium2TestCase
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
            ->assertTitle('Create Tag - Tags - System')
            ->setTagname($tagname)
            ->save()
            ->assertMessage('Tag successfully saved')
            ->assertTitle('Tags')
            ->close();

        return $tagname;
    }

    /**
     * @depends testCreateTag
     * @param $tagname
     * @return string
     */
    public function testUpdateTag($tagname)
    {
        $newtagname = 'Update_' . $tagname;
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTags()
            ->filterBy('Tag', $tagname)
            ->edit()
            ->setTagname($newtagname)
            ->save()
            ->assertTitle('Tags - System')
            ->assertMessage('Tag successfully saved');

        return $newtagname;
    }

    /**
     * @depends testUpdateTag
     * @param $tagname
     */
    public function testDeleteTag($tagname)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openTags()
            ->filterBy('Tag', $tagname)
            ->delete()
            ->assertTitle('Tags - System')
            ->assertMessage('Item was deleted');
    }
}
