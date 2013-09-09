<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\Views;

use Oro\Bundle\GridBundle\Datagrid\Views\View;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'some.name';

    /** @var View */
    protected $view;

    public function setUp()
    {
        $this->view = new View(self::TEST_NAME);
    }

    public function tearDown()
    {
        unset($this->view);
    }

    public function testGetName()
    {
        $this->assertEquals(self::TEST_NAME, $this->view->getName());
    }

    public function testPassingFiltersData()
    {
        $filtersData = array(
            'some_filter' => array(
                'value' => 'someValue'
            )
        );
        // passing through setter
        $this->assertEmpty($this->view->getFiltersData());
        $this->view->setFiltersData($filtersData);
        $this->assertEquals($filtersData, $this->view->getFiltersData());

        //passing through constructor
        $view = new View(self::TEST_NAME, $filtersData);
        $this->assertEquals($filtersData, $view->getFiltersData());
    }

    public function testPassingSortersData()
    {
        $sortersData = array(
            'some_field' => 'ASC'
        );
        // passing through setter
        $this->assertEmpty($this->view->getSortersData());
        $this->view->setSortersData($sortersData);
        $this->assertEquals($sortersData, $this->view->getSortersData());

        //passing through constructor
        $view = new View(self::TEST_NAME, array(), $sortersData);
        $this->assertEquals($sortersData, $view->getSortersData());
    }
}
