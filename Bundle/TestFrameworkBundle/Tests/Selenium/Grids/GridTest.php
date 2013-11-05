<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Users;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class GridTest extends Selenium2TestCase
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


    public function testSelectPage()
    {
        $this->markTestIncomplete('Exception is occurred during test');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        $users = new Users($this);
        $userData = $users->getRandomEntity();
        $this->assertTrue($users->entityExists($userData));
        $users->changePage(2);
        $this->assertFalse($users->entityExists($userData));
        $users->changePage(1);
        $this->assertTrue($users->entityExists($userData));
    }

    public function testNextPage()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        $users = new Users($this);
        $userData = $users->getRandomEntity();
        $this->assertTrue($users->entityExists($userData));
        $users->nextPage();
        $this->assertFalse($users->entityExists($userData));
        $users->previousPage();
        $this->assertTrue($users->entityExists($userData));
    }

    public function testPrevPage()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        $users = new Users($this);
        $userData = $users->getRandomEntity();
        $this->assertTrue($users->entityExists($userData));
        $users->nextPage();
        $this->assertFalse($users->entityExists($userData));
        $users->previousPage();
        $this->assertTrue($users->entityExists($userData));
    }

    /**
     * @dataProvider filterData
     */
    public function testFilterBy($filterName, $condition)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        $users = new Users($this);
        $userData = $users->getRandomEntity();
        $this->assertTrue(
            $users->filterBy($filterName, $userData[strtoupper($filterName)], $condition)
                ->entityExists($userData)
        );
        $this->assertEquals(1, $users->getRowsCount());
        $users->clearFilter($filterName);
    }

    /**
     * Data provider for filter tests
     *
     * @return array
     */
    public function filterData()
    {
        return array(
            //'ID' => array('ID', '='),
            'Username' => array('Username', 'is equal to'),
            'Email' => array('Email', 'contains'),
            //'First name' => array('First name', 'is equal to'),
            //'Birthday' => array('Birthday', '')
        );
    }

    public function testAddFilter()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        $users = new Users($this);
        $userData = $users->getRandomEntity();
        $this->assertTrue($users->entityExists($userData));
        $countOfRecords = $users->getRowsCount();
        $this->assertEquals(
            $countOfRecords,
            $users->removeFilter('Email')
                ->getRowsCount()
        );

        $this->assertEquals(
            1,
            $users->addFilter('Email')
                ->filterBy('Email', $userData[strtoupper('Email')], 'is equal to')
                ->getRowsCount()
        );
    }

    /**
     * Tests that order in columns works correct
     *
     * @param string $columnName
     * @dataProvider columnTitle
     */
    public function testSorting($columnName)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();
        $users = new Users($this);
        $users->changePageSize('last');
        $columnId = $users->getColumnNumber($columnName);

        //test descending order
        $columnOrder = $users->sortBy($columnName, 'desc')->getColumn($columnId);

        if ($columnName == 'Birthday') {
            $dateArray = array();
            foreach ($columnOrder as $value) {
                $date = strtotime($value);
                $dateArray[] = $date;
            }
            $columnOrder = $dateArray;
        }
        $sortedColumnOrder = $columnOrder;
        natcasesort($sortedColumnOrder);
        $sortedColumnOrder = array_reverse($sortedColumnOrder);

        $this->assertTrue(
            $columnOrder === $sortedColumnOrder,
            print_r(array('expected' => $sortedColumnOrder, 'actual' => $columnOrder), true)
        );
        //change page size to 10 and refresh grid
        $users->changePageSize('first');
        $users->sortBy($columnName, 'asc');
        $columnOrder = $users->sortBy($columnName, 'desc')->getColumn($columnId);
        $this->assertTrue(
            $columnOrder === array_slice($sortedColumnOrder, 0, 10),
            print_r(array('expected' => $sortedColumnOrder, 'actual' => $columnOrder), true)
        );

        //test ascending order
        $users->changePageSize('last');
        $columnOrder = $users->sortBy($columnName, 'asc')->getColumn($columnId);

        if ($columnName == 'Birthday') {
            $dateArray = array();
            foreach ($columnOrder as $value) {
                $date = strtotime($value);
                $dateArray[] = $date;
            }
            $columnOrder = $dateArray;
        }
        $sortedColumnOrder = $columnOrder;
        natcasesort($sortedColumnOrder);

        $this->assertTrue(
            $columnOrder === $sortedColumnOrder,
            print_r(array('expected' => $sortedColumnOrder, 'actual' => $columnOrder), true)
        );
        //change page size to 10 and refresh grid
        $users->changePageSize('first');
        $users->sortBy($columnName, 'desc');
        $columnOrder = $users->sortBy($columnName, 'asc')->getColumn($columnId);
        $this->assertTrue(
            $columnOrder === array_slice($sortedColumnOrder, 0, 10),
            print_r(array('expected' => $sortedColumnOrder, 'actual' => $columnOrder), true)
        );
    }

    /**
     * Data provider for test sorting
     *
     * @return array
     */
    public function columnTitle()
    {
        return array(
            //'ID' => array('ID'),
            'Username' => array('Username'),
            //'Email' => array('Email'),
            //'First name' => array('First name'),
            //'Birthday' => array('Birthday'),
            //'Company' => array('Company'),
            //'Salary' => array('Salary'),
        );
    }
}
