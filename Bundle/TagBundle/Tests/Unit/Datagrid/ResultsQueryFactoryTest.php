<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Datagrid;

use Oro\Bundle\TagBundle\Datagrid\ResultsQueryFactory;

class ResultsQueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $qb;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var ResultsQueryFactory
     */
    protected $queryFactory;

    public function setUp()
    {
        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryFactory = new ResultsQueryFactory($this->registry, 'testClassName', $this->mapper);
    }

    public function testCreateQuery()
    {
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('testClassName')
            ->will($this->returnValue($this->em));

        $repositoryMock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with('testClassName')
            ->will($this->returnValue($repositoryMock));

        $repositoryMock->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->assertInstanceOf('Oro\Bundle\TagBundle\Datagrid\ResultsQuery', $this->queryFactory->createQuery());
    }
}
