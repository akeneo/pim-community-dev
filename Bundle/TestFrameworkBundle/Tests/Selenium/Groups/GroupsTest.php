<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class GroupsTest extends Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;
    
    protected $newGroup = array('NAME' => 'NEW_GROUP_', 'ROLE' => 'Administrator');

    protected $defaultGroups = array(
        'header' => array('NAME' => 'NAME', 'ROLES' => 'ROLES', '' => 'ACTION'),
        'Administrators' => array('Administrators' => 'Administrators', 'Administrator' => 'ROLES', '...' => 'ACTION'),
        'Marketing' => array('Marketing' => 'Marketing', 'Manager' => 'ROLES', '...' => 'ACTION'),
        'Sales' => array('Sales' => 'Sales', 'Manager' => 'ROLES', '...' => 'ACTION')
    );

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

    public function testGroupsGrid()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups()
            ->assertTitle('Groups - Users Management - System');
    }

    public function testGroupsGridDefaultContent()
    {
        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups();
        //get grid content
        $records = $groups->getRows();
        $headers = $groups->getHeaders();

        foreach ($headers as $header) {
            $content = $header->text();
            $this->assertArrayHasKey($content, $this->defaultGroups['header']);
        }

        $checks = 0;
        foreach ($records as $row) {
            $columns = $row->elements($this->using('xpath')->value("td[not(contains(@style, 'display: none;'))]"));
            $id = null;
            foreach ($columns as $column) {
                $content = $column->text();
                if (is_null($id)) {
                    $id = $content;
                }
                if (array_key_exists($id, $this->defaultGroups)) {
                    $this->assertArrayHasKey($content, $this->defaultGroups[$id]);
                }
            }
            $checks = $checks + 1;
        }
        $this->assertGreaterThanOrEqual(count($this->defaultGroups)-1, $checks);
    }

    public function testGroupAdd()
    {
        $randomPrefix = ToolsAPI::randomGen(5);

        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups()
            ->add()
            ->setName($this->newGroup['NAME'] . $randomPrefix)
            ->setOwner('Main')
            //->setRoles(array($this->newGroup['ROLE']))
            ->save()
            ->assertMessage('Group saved')
            ->close();

        $this->assertTrue($groups->entityExists(array('name' => $this->newGroup['NAME'] . $randomPrefix)));

        return $randomPrefix;
    }

    /**
     * @depends testGroupAdd
     * @param $randomPrefix
     */
    public function testGroupDelete($randomPrefix)
    {
        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups();
        $groups->deleteEntity(array('name' => $this->newGroup['NAME'] . $randomPrefix));
        $this->assertFalse($groups->entityExists(array('name' => $this->newGroup['NAME'] . $randomPrefix)));
    }
}
