<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

class ProxyQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProxyQuery
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetQueryBuilder()
    {
        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
        $this->model = new ProxyQuery($queryBuilderMock);
        $this->assertEquals($queryBuilderMock, $this->model->getQueryBuilder());
    }

    public function testSetParameter()
    {
        $testName  = 'test_name';
        $testValue = 'test_value';

        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array('setParameter'), array(), '', false);
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with($testName, $testValue);

        $this->model = new ProxyQuery($queryBuilderMock);
        $this->model->setParameter($testName, $testValue);
    }
}
