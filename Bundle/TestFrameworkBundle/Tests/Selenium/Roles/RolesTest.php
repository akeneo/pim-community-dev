<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class RolesTest extends Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected $newRole = array('LABEL' => 'NEW_LABEL_', 'ROLE_NAME' => 'NEW_ROLE_');

    protected $defaultRoles = array(
        'header' => array('ROLE' => 'ROLE', 'LABEL' => 'LABEL', '' => 'ACTION'),
        'ROLE_MANAGER' => array('ROLE_MANAGER' => 'ROLE_MANAGER', 'Manager' => 'Manager', '...' => 'ACTION'),
        'ROLE_ADMINISTRATOR' => array('ROLE_ADMINISTRATOR' => 'ROLE_ADMINISTRATOR', 'Administrator' => 'Administrator', '...' => 'ACTION'),
        'ROLE_USER' => array('ROLE_USER' => 'ROLE_USER', 'User' => 'User', '...' => 'ACTION')
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

    public function testRolesGrid()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles()
            ->assertTitle('Roles - Users Management - System');
    }

    public function testRolesGridDefaultContent()
    {
        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles();
        //get grid content
        $records = $groups->getRows();
        $headers = $groups->getHeaders();

        foreach ($headers as $header) {
            $content = $header->text();
            $this->assertArrayHasKey($content, $this->defaultRoles['header']);
        }

        $checks = 0;
        foreach ($records as $row) {
            $columns = $row->elements($this->using('xpath')->value("td[not(contains(@style, 'display: none;'))]"));
            $id = null;
            foreach ($columns as $column) {
                $content = $column->text();
                if (is_null($id)) {
                    $id = trim($content);
                }
                if (array_key_exists($id, $this->defaultRoles)) {
                    $this->assertArrayHasKey($content, $this->defaultRoles[$id]);
                }
            }
            $checks = $checks + 1;
        }
        $this->assertGreaterThanOrEqual(count($this->defaultRoles)-1, $checks);
    }

    public function testRolesAdd()
    {

        $randomPrefix = ToolsAPI::randomGen(5);

        $login = new Login($this);
        $roles = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles()
            ->add()
            ->setLabel($this->newRole['LABEL'] . $randomPrefix)
            ->setOwner('Main')
            ->save()
            ->assertMessage('Role saved')
            ->close();

        //verify new Role
        $roles->refresh();

        $this->assertTrue($roles->entityExists(array('name' => $this->newRole['LABEL'] . $randomPrefix)));

        return $randomPrefix;
    }

    /**
     * @depends testRolesAdd
     * @param $randomPrefix
     */
    public function testRoleDelete($randomPrefix)
    {
        $login = new Login($this);
        $roles = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles();
        $roles->deleteEntity(array('name' => $this->newRole['LABEL'] . $randomPrefix));
        $this->assertFalse($roles->entityExists(array('name' => $this->newRole['LABEL'] . $randomPrefix)));
    }
}
