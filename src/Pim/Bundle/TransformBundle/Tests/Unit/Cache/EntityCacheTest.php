<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Cache;

use Pim\Bundle\TransformBundle\Cache\EntityCache;

/**
 * Tests EntityCache
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $doctrine;
    protected $cache;
    protected $repository;
    protected $referenceRepository;

    protected $doctrineEntities;
    protected $fetchedEntities;
    protected $referencedEntities;

    protected function setUp()
    {
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->cache = new EntityCache($this->doctrine);
        $this->repository = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrine->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('class'))
            ->will($this->returnValue($this->repository));
        $this->doctrineEntities = array();
        $this->fetchedEntities = array();
        $this->repository->expects($this->any())
            ->method('findByReference')
            ->will(
                $this->returnCallback(
                    function ($code) {
                        $this->assertNotContains($code, $this->fetchedEntities);
                        $this->fetchedEntities[] = $code;

                        return $this->doctrineEntities[$code];
                    }
                )
            );

        $this->referencedEntities = array();
        $this->referenceRepository = $this->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->referenceRepository->expects($this->any())
            ->method('getReference')
            ->will(
                $this->returnCallback(
                    function ($code) {
                        $this->assertNotContains($code, $this->fetchedEntities);
                        $this->fetchedEntities[] = $code;

                        return $this->referencedEntities[$code];
                    }
                )
            );
        $this->referenceRepository->expects($this->any())
            ->method('hasReference')
            ->will(
                $this->returnCallback(
                    function ($code) {
                        return isset($this->referencedEntities[$code]);
                    }
                )
            );
    }

    /**
     * Test related method
     */
    public function testFind()
    {
        $this->addDoctrineEntity('code1');
        $this->addDoctrineEntity('code2');
        $this->assertDoctrineFind('code1');
        $this->assertDoctrineFind('code2');

        //Test that the values are not queried a second time
        $this->assertDoctrineFind('code1');
        $this->assertDoctrineFind('code2');
    }

    public function testFindReferencedEntities()
    {
        $this->cache->setReferenceRepository($this->referenceRepository);
        $this->addDoctrineEntity('code1');
        $this->addReferencedEntity('code2');

        $this->assertDoctrineFind('code1');
        $this->assertReferenceFind('code2');
    }

    /**
     * Test related method
     */
    public function testReset()
    {
        $this->addDoctrineEntity('code1');
        $this->addDoctrineEntity('code2');
        $this->assertDoctrineFind('code1');
        $this->assertDoctrineFind('code2');

        $this->cache->clear();
        $this->fetchedEntities = array();

        $this->addDoctrineEntity('code1');
        $this->addDoctrineEntity('code2');
        $this->assertDoctrineFind('code1');
        $this->assertDoctrineFind('code2');
    }

    public function testSetReference()
    {
        $object = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ReferableInterface')
            ->setMockClassName('referable')
            ->getMock();
        $object->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue('reference'));
        $this->referenceRepository
            ->expects($this->any())
            ->method('setReference')
            ->with($this->equalTo('referable.reference'), $this->identicalTo($object));
        $this->cache->setReferenceRepository($this->referenceRepository);
        $this->cache->setReference($object);
    }

    protected function assertDoctrineFind($code)
    {
        $this->assertSame($this->doctrineEntities[$code], $this->cache->find('class', $code));
    }

    protected function assertReferenceFind($code)
    {
        $this->assertSame($this->referencedEntities['class.' . $code], $this->cache->find('class', $code));
    }

    protected function addDoctrineEntity($code)
    {
        $this->doctrineEntities[$code] = new \stdClass;
    }

    protected function addReferencedEntity($code)
    {
        $this->referencedEntities['class.' . $code] = new \stdClass;
    }
}
