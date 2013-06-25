<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\AddressBundle\Entity\Repository\RegionRepository;
use Oro\Bundle\AddressBundle\Entity\Country;

class RegionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAME = 'RegionEntityName';

    /**
     * @var RegionRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $testRegions = array('one', 'two', 'three');

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $this->repository = new RegionRepository($this->entityManager, new ClassMetadata(self::ENTITY_NAME));
    }

    protected function tearDown()
    {
        unset($this->repository);
    }

    /**
     * Tests both getCountryRegionsQueryBuilder and getCountryRegions
     */
    public function testGetCountryRegions()
    {
        $entityAlias = 'r';
        $country = new Country('iso2Code');

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('setHint', 'execute'))
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('setHint')->with(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->expects($this->once())->method('execute')
            ->will($this->returnValue($this->testRegions));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'from', 'where', 'orderBy', 'setParameter', 'getQuery'))
            ->getMock();
        $queryBuilder->expects($this->once())->method('select')->with($entityAlias)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('from')->with(self::ENTITY_NAME, $entityAlias)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('where')->with('r.country = :country')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('orderBy')->with('r.name', 'ASC')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('setParameter')->with('country', $country)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('getQuery')
            ->will($this->returnValue($query));

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $actualRegions = $this->repository->getCountryRegions($country);
        $this->assertEquals($this->testRegions, $actualRegions);
    }
}
