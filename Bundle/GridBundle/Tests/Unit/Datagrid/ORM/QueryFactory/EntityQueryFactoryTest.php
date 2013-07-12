<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM\QueryFactory;

use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

class EntityQueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_CLASS_NAME = 'TestClassName';
    const TEST_ALIAS      = 'test_alias';

    /**
     * @var EntityQueryFactory
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param array $arguments
     */
    protected function initializeEntityQueryFactory($arguments = array())
    {
        $defaultArguments = array(
            'registry'  => $this->getMockForAbstractClass('Symfony\Bridge\Doctrine\RegistryInterface'),
            'className' => null,
            'alias'     => null,
        );
        $arguments = array_merge($defaultArguments, $arguments);

        $this->model = new EntityQueryFactory($arguments['registry'], $arguments['className'], $arguments['alias']);
    }

    public function testGetClassName()
    {
        $this->initializeEntityQueryFactory(array('className' => self::TEST_CLASS_NAME));
        $this->assertEquals(self::TEST_CLASS_NAME, $this->model->getClassName());
    }

    public function testGetAlias()
    {
        $this->initializeEntityQueryFactory(array('alias' => self::TEST_ALIAS));
        $this->assertEquals(self::TEST_ALIAS, $this->model->getAlias());
    }

    public function testCreateQuery()
    {
        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);

        $repositoryMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('createQueryBuilder'),
            array(),
            '',
            false
        );
        $repositoryMock->expects($this->once())
            ->method('createQueryBuilder')
            ->with(self::TEST_ALIAS)
            ->will($this->returnValue($queryBuilderMock));

        $entityManagerMock = $this->getMock('Doctrine\ORM\EntityManager', array('getRepository'), array(), '', false);
        $entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(self::TEST_CLASS_NAME)
            ->will($this->returnValue($repositoryMock));

        $registryMock = $this->getMockForAbstractClass(
            'Symfony\Bridge\Doctrine\RegistryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getManagerForClass')
        );
        $registryMock->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::TEST_CLASS_NAME)
            ->will($this->returnValue($entityManagerMock));

        // test
        $this->initializeEntityQueryFactory(
            array('registry' => $registryMock, 'className' => self::TEST_CLASS_NAME, 'alias' => self::TEST_ALIAS)
        );
        $proxyQuery = $this->model->createQuery();

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery', $proxyQuery);
        $this->assertAttributeEquals($queryBuilderMock, 'queryBuilder', $proxyQuery);
    }
}
