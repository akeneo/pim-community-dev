<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SearchBundle\Datagrid\SearchDatagridManager;

class SearchDatagridManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var SearchDatagridManager */
    protected $datagridManager;

    protected function setUp()
    {
        $this->markTestSkipped("TODO Fix or remove");

        $this->datagridManager = new SearchDatagridManager('test_template');
    }

    protected function tearDown()
    {
        unset($this->datagridManager);
    }

    public function testSetSearchEntity()
    {
        $wildcardEntity = '*';
        $specificEntity = 'oro_user';

        // default entity
        $this->assertAttributeEquals($wildcardEntity, 'searchEntity', $this->datagridManager);

        // specific entity
        $this->datagridManager->setSearchEntity($specificEntity);
        $this->assertAttributeEquals($specificEntity, 'searchEntity', $this->datagridManager);

        // all entities
        $this->datagridManager->setSearchEntity(null);
        $this->assertAttributeEquals($wildcardEntity, 'searchEntity', $this->datagridManager);
    }

    public function testSetSearchString()
    {
        $searchString = 'test search string';

        $this->datagridManager->setSearchString($searchString);
        $this->assertAttributeEquals($searchString, 'searchString', $this->datagridManager);
    }
}
