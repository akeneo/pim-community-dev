<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\Pager;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pager
     */
    protected $model;

    /**
     * @var ProxyQueryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    /**
     * @var array
     */
    protected $complexFields = array(
        'key1' => 'value1',
        'key2' => 'value2',
    );

    protected function setUp()
    {
        $this->query = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $this->model = new Pager();
        $this->model->setQuery($this->query);
    }

    protected function tearDown()
    {
        unset($this->query);
        unset($this->model);
    }

    public function testComputeNbResult()
    {
        $totalCount = 100;
        $this->query->expects($this->once())
            ->method('getTotalCount')
            ->will($this->returnValue($totalCount));
        $this->assertEquals($totalCount, $this->model->computeNbResult());
    }
}
