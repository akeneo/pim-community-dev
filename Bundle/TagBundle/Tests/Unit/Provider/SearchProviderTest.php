<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Provider;

use Oro\Bundle\TagBundle\Provider\SearchProvider;

class SearchProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ID = 1;
    const TEST_ENTITY_NAME = 'name';

    /** @var SearchProvider */
    protected $provider;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $mapper;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManager;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()->getMock();
        $this->provider = new SearchProvider($this->entityManager, $this->mapper);
    }

    public function tearDown()
    {
        unset($this->entityManager);
        unset($this->mapper);
        unset($this->provider);
    }

    public function testGetResults()
    {
        $taggingMock = $this->getMock('Oro\Bundle\TagBundle\Entity\Tagging');
        $taggingMock->expects($this->exactly(2))->method('getEntityName')
            ->will($this->returnValue(self::TEST_ENTITY_NAME));

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getResult')
            ->will($this->returnValue(array($taggingMock)));

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()->getMock();
        $qb->expects($this->once())->method('select')
            ->will($this->returnSelf());
        $qb->expects($this->once())->method('from')
            ->will($this->returnSelf());
        $qb->expects($this->once())->method('where')
            ->will($this->returnSelf());
        $qb->expects($this->exactly(2))->method('addGroupBy')
            ->will($this->returnSelf());
        $qb->expects($this->once())->method('setParameter')
            ->will($this->returnSelf());
        $qb->expects($this->once())->method('getQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        $this->mapper->expects($this->once())->method('getEntityConfig')->with(self::TEST_ENTITY_NAME);

        $this->assertInstanceOf('Oro\Bundle\SearchBundle\Query\Result', $this->provider->getResults(self::TEST_ID));
    }
}
