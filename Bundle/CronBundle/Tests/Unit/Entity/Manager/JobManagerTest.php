<?php

namespace Oro\Bundle\CronBundle\Entity;

use JMS\JobQueueBundle\Entity\Job;

class JobManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobManager
     */
    protected $object;

    /**
     * @var Job
     */
    protected $job;

    protected function setUp()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs(array($em))
            ->enableArgumentCloning()
            ->getMock();

        $expr  = $this->getMock('Doctrine\ORM\Query\Expr');
        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $qb->expects($this->any())->method('select')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('from')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('where')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('orderBy')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('expr')->will($this->returnValue($expr));

        $em->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        $em->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($conn));

        $em->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo('Oro\Bundle\CronBundle\Entity\Schedule'))
            ->will($this->returnValue($class));

        $conn->expects($this->any())
            ->method('query')
            ->will($this->returnValue(array(
                array(
                    'characteristic' => 'memory',
                    'createdAt'      => '2013-08-16 14:51:08',
                    'charValue'      => '818759584'
                )
            )));

        $this->object = new Manager\JobManager($em);
        $this->job    = new Job('oro:test');
    }

    public function testGetListQuery()
    {
        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $this->object->getListQuery());
    }

    public function testGetRelatedEntities()
    {
        $relEntity = new Schedule();

        $this->assertInternalType('array', $this->object->getRelatedEntities($this->job));
        $this->assertEmpty($this->object->getRelatedEntities($this->job));

        $this->job->addRelatedEntity($relEntity);

        $this->assertNotEmpty($this->object->getRelatedEntities($this->job));
    }

    public function testGetJobStatistics()
    {
        $stat = $this->object->getJobStatistics($this->job);

        $this->assertInternalType('array', $stat);
        $this->assertNotEmpty($stat);
        $this->assertEquals('Time', $stat[0][0]);
        $this->assertEquals('memory', $stat[0][1]);
        $this->assertInternalType('float', $stat[1][1]);
        $this->assertEquals(780, (int) $stat[1][1]);
    }
}
