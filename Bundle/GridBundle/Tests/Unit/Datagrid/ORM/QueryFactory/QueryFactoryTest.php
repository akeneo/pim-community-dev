<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM\QueryFactory;

use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\QueryFactory;

class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryFactory
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetQueryBuilder()
    {
        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
        $this->model = new QueryFactory();
        $this->model->setQueryBuilder($queryBuilderMock);

        $this->assertAttributeEquals($queryBuilderMock, 'queryBuilder', $this->model);
    }

    public function testCreateQuery()
    {
        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
        $this->model = new QueryFactory($queryBuilderMock);
        $proxyQuery = $this->model->createQuery();

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery', $proxyQuery);
        $this->assertAttributeEquals($queryBuilderMock, 'queryBuilder', $proxyQuery);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Can't create datagrid query. Query builder is not configured.
     */
    public function testCreateQueryFails()
    {
        $this->model = new QueryFactory();
        $this->model->createQuery();
    }
}
