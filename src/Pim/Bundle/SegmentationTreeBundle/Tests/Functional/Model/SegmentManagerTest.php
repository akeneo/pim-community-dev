<?php

namespace Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Model;

use Pim\Bundle\SegmentationTreeBundle\Tests\Functional\DataFixtures\ORM\LoadItemSegmentData;

use Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\Item;
use Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\ItemSegment;
use Pim\Bundle\SegmentationTreeBundle\Manager\SegmentManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

use Doctrine\Common\EventManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Test related class
 *
 * TODO: Create a workaround for drop/create schema because it removes acl tables and breaks further tests
 */
class SegmentManagerTest extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ORM\Tools\SchemaTool;
     */
    protected $schemaTool;

    /**
     * @staticvar
     */
    static protected $itemEntityName = 'Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\Item';

    /**
     * @staticvar
     */
    static protected $itemSegmentEntityName = 'Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\ItemSegment';

    /**
     * @var SegmentManager
     */
    protected $segmentManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markTestSkipped('Requires refactoring');
        $entityPath = dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'Entity';
        static::$kernel = static::createKernel(array("debug" => true));
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();

        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, $entityPath);

        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);

        $this->schemaTool = new SchemaTool($this->em);

        $this->initializeDatabase();

        $this->segmentManager = new SegmentManager($this->em, static::$itemSegmentEntityName);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        // Remove specific tables used only for the tests
        //$classes = $this->em->getMetadataFactory()->getAllMetadata();
        //$this->schemaTool->dropSchema($classes);
    }

    /**
     * Initialize database dropping existent and create tables
     */
    protected function initializeDatabase()
    {

        $classes = $this->em->getMetadataFactory()->getAllMetadata();

        // disable foreign keys check
        $connection = $this->em->getConnection();

        // FIXME:
        // Ugly hack because of JMS/JobQueueBundle that tries to create a foreign key
        // to the jms_jobs table although it does not exist because the AnnotationReader
        // is configured to manage only our Test entity.
        // See JMS\JobQueueBundle\Entity\Listener\ManyToAnyListener:postGenerateSchema()
        $connection->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->schemaTool->dropSchema($classes);
        $this->schemaTool->createSchema($classes);
        $connection->query('SET FOREIGN_KEY_CHECKS = 1');

        $fixture = new LoadItemSegmentData();
        $fixture->setContainer($this->container);
        $fixture->load($this->em);
    }

    /**
     * Test related method
     */
    public function testGetChildren()
    {
        $segment2 = $this->em->find(static::$itemSegmentEntityName, 4);
        $segment3 = $this->em->find(static::$itemSegmentEntityName, 5);
        $segment4 = $this->em->find(static::$itemSegmentEntityName, 6);

        $expectedChildrenIds = array($segment3->getId(), $segment4->getId());

        $actualChildren = $this->segmentManager->getChildren($segment2);

        $this->assertCount(count($expectedChildrenIds), $actualChildren);

        foreach ($actualChildren as $actualChild) {
            $this->assertTrue(in_array($actualChild->getId(), $expectedChildrenIds));
        }
    }

    /**
     * Test related method
     */
    public function testRemoveById()
    {
        $idToRemove = 8;
        $segmentsBefore = $this->em->getRepository(static::$itemSegmentEntityName)->findAll();

        $expectedCount = count($segmentsBefore) - 1;

        $this->segmentManager->removeById($idToRemove);
        $this->em->flush();

        $segmentsAfter = $this->em->getRepository(static::$itemSegmentEntityName)->findAll();

        $this->assertCount($expectedCount, $segmentsAfter);

        foreach ($segmentsAfter as $segment) {
            $this->assertNotEquals($segment->getId(), $idToRemove);
        }
    }

    /**
     * Test related method
     */
    public function testRemove()
    {
        $idToRemove = 7;
        $segment = $this->em->find(static::$itemSegmentEntityName, $idToRemove);

        $segmentsBefore = $this->em->getRepository(static::$itemSegmentEntityName)->findAll();

        $expectedCount = count($segmentsBefore) - 1;

        $this->segmentManager->remove($segment);
        $this->em->flush();

        $segmentsAfter = $this->em->getRepository(static::$itemSegmentEntityName)->findAll();

        $this->assertCount($expectedCount, $segmentsAfter);

        foreach ($segmentsAfter as $segment) {
            $this->assertNotEquals($segment->getId(), $idToRemove);
        }
    }

    /**
     * Test related method
     */
    public function testRename()
    {
        $idToRename = 6;
        $newCode = "My new code";

        $segment = $this->em->find(static::$itemSegmentEntityName, $idToRename);

        $this->assertNotEquals($newCode, $segment->getCode());

        $this->segmentManager->rename($idToRename, $newCode);
        $this->em->flush();

        $segment = $this->em->find(static::$itemSegmentEntityName, $idToRename);
        $this->assertEquals($newCode, $segment->getCode());
    }

    /**
     * Test related method
     */
    public function testMoveParent()
    {
        $idToMove = 5;
        $newParentId = 6;
        $segment = $this->em->find(static::$itemSegmentEntityName, $idToMove);

        $this->assertNotEquals($newParentId, $segment->getParent()->getId());

        $this->segmentManager->move($idToMove, $newParentId, null);
        $this->em->flush();

        $segment = $this->em->find(static::$itemSegmentEntityName, $idToMove);

        $this->assertEquals($newParentId, $segment->getParent()->getId());
    }

    /**
     * Test related method
     */
    public function testGetTrees()
    {
        $expectedTreesIds = array(1,3);

        $trees = $this->segmentManager->getTrees();

        $this->assertEquals(count($expectedTreesIds), count($trees));

        foreach ($trees as $tree) {
            $this->assertTrue(in_array($tree->getId(), $expectedTreesIds));
        }
    }

    /**
     * Test related method
     */
    public function testSearch()
    {
        $rootNode2 = $this->em->find(static::$itemSegmentEntityName, 3);
        $segment2 = $this->em->find(static::$itemSegmentEntityName, 4);

        $expectedResultsIds = array($rootNode2->getId(), $segment2->getId());

        $actualResults = $this->segmentManager->search(3, array('code' => 'Two'));

        $this->assertCount(count($expectedResultsIds), $actualResults);

        foreach ($actualResults as $actualResult) {
            $this->assertTrue(in_array($actualResult->getId(), $expectedResultsIds));
        }
    }

    /**
     * Test related method
     */
    public function testSearchOnASingleTree()
    {
        $results = $this->segmentManager->search(1, array('code' => 'Segment'));

        $this->assertCount(1, $results);

        $results = $this->segmentManager->search(3, array('code' => 'Segment'));

        $this->assertCount(5, $results);

    }

    /**
     * Test related method
     */
    public function testGetTreeSegments()
    {
        $rootSegment = $this->em->find(static::$itemSegmentEntityName, 3);

        $expectedTreeSegmentsIds = array(3,4,5,6,7,8);

        $actualTreeSegments = $this->segmentManager->getTreeSegments($rootSegment);

        $this->assertCount(count($expectedTreeSegmentsIds), $actualTreeSegments);

        foreach ($actualTreeSegments as $actualTreeSegment) {
            $this->assertTrue(in_array($actualTreeSegment->getId(), $expectedTreeSegmentsIds));
        }

    }

    /**
     * Test related method
     */
    public function testCreateTree()
    {
        $newTreeCode= 'My super new tree';

        $newTreeRoot = $this->segmentManager->createTree($newTreeCode);
        $this->em->flush();

        $expectedTreesIds = array(1, 3, $newTreeRoot->getId());

        $trees = $this->segmentManager->getTrees();

        $this->assertEquals(count($expectedTreesIds), count($trees));

        foreach ($trees as $tree) {
            $this->assertTrue(in_array($tree->getId(), $expectedTreesIds));
        }
    }

    /**
     * Test related method
     */
    public function testRemoveTree()
    {
        $treeIdToRemove = 3;
        $segmentToRemove = $this->em->find(static::$itemSegmentEntityName, $treeIdToRemove);
        $this->removeAndAssertTreeRemoved($segmentToRemove);
    }

    /**
     * Test related method
     */
    public function testRemoveTreeById()
    {
        $treeIdToRemove = 3;
        $segmentToRemove = $this->em->find(static::$itemSegmentEntityName, $treeIdToRemove);
        $this->removeAndAssertTreeRemoved($segmentToRemove, true);
    }

    /**
     * Remove a segment and assert tree
     * @param CategoryInterface $segment    Segment removed
     * @param boolean         $removeById Predicate to remove by id or not
     */
    private function removeAndAssertTreeRemoved($segment, $removeById = false)
    {
        $segmentsBefore = $this->em->getRepository(static::$itemSegmentEntityName)->findAll();
        $segmentsCountBefore = count($segmentsBefore);

        $treeSegments = $this->segmentManager->getTreeSegments($segment);
        $treeSegmentsIds = array();

        foreach ($treeSegments as $treeSegment) {
            $treeSegmentsIds[] = $treeSegment->getId();
        }

        if ($removeById) {
            $this->segmentManager->removeTreeById($segment->getId());
        } else {
            $this->segmentManager->removeTree($segment);
        }

        $this->em->flush();

        $segmentsAfter = $this->em->getRepository(static::$itemSegmentEntityName)->findAll();
        $segmentsCountAfter = count($segmentsAfter);

        $expectedCount = $segmentsCountBefore - count($treeSegments);

        $this->assertEquals($expectedCount, $segmentsCountAfter);

        foreach ($segmentsAfter as $segmentAfter) {
            $this->assertFalse(in_array($segmentAfter->getId(), $treeSegmentsIds));
        }
    }
}
