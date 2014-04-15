<?php
namespace Pim\Bundle\SegmentationTreeBundle\Tests\Unit\Model;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\DependencyInjection\Container;

use Pim\Bundle\SegmentationTreeBundle\Manager\SegmentManager;
use Pim\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;

/**
 * Tests on SegmentManager
 *
 *
 */
class SegmentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractSegment $segmentManager
     */
    protected $segment;

    /**
     * @var SegmentManager $segmentManager
     */
    protected $segmentManager;

    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $storageManager;

    /**
     * @var Pim\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository
     */
    protected $entityRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->entityRepository =
                $this->getMockBuilder('Pim\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository')
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->storageManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->storageManager->expects($this->any())
             ->method('getRepository')
             ->will($this->returnValue($this->entityRepository));
        $this->segment = $this->getMockForAbstractClass('Pim\Bundle\SegmentationTreeBundle\Entity\AbstractSegment');
        $this->segmentManager = new SegmentManager($this->storageManager, get_class($this->segment));
    }

    /**
     * Test related method
     */
    public function testGetStorageManager()
    {
        $this->assertEquals($this->segmentManager->getStorageManager(), $this->storageManager);
    }

    /**
     * Test related method
     */
    public function testCreateSegment()
    {
        $actualClassName = get_class($this->segmentManager->getSegmentInstance());
        $this->assertEquals($actualClassName, get_class($this->segment));
    }

    /**
     * Test related method
     */
    public function testGetSegmentName()
    {
        $this->assertEquals($this->segmentManager->getSegmentName(), get_class($this->segment));
    }

    /**
     * Test related method
     */
    public function testGetEntityRepository()
    {
        $this->assertEquals($this->segmentManager->getEntityRepository(), $this->entityRepository);
    }

    /**
     * Test related method
     */
    public function testCopyInstance()
    {
        $rootNode = $this->segmentManager->getSegmentInstance();

        $node = $this->segmentManager->getSegmentInstance();
        $node->setCode('parent node');
        $node->setParent($rootNode);
        $rootNode->addChild($node);

        $firstChild = $this->segmentManager->getSegmentInstance();
        $firstChild->setCode('first child');
        $firstChild->setParent($node);
        $node->addChild($firstChild);

        $secondChild = $this->segmentManager->getSegmentInstance();
        $secondChild->setCode('second child');
        $secondChild->setParent($node);
        $node->addChild($secondChild);

        $firstGrandChild = $this->segmentManager->getSegmentInstance();
        $firstGrandChild->setCode('first grand child');
        $firstGrandChild->setParent($secondChild);
        $secondChild->addChild($firstGrandChild);

        $nodeCopy = $this->segmentManager->copyNode($node, $rootNode);
        $this->assertEquals($node, $nodeCopy);

        $copyChildren = $nodeCopy->getChildren();
        $copyFirstChild = $copyChildren[0];
        $this->assertEquals($copyFirstChild, $firstChild);

        $copySecondChild = $copyChildren[1];
        $this->assertEquals($copySecondChild, $secondChild);

        $copyGrandChildren = $copySecondChild->getChildren();
        $copyFirstGrandChild = $copyGrandChildren[0];
        $this->assertEquals($copyFirstGrandChild, $firstGrandChild);
    }

    /**
     * Test related method
     */
    public function testGetChildren()
    {
        $firstChild = $this->segmentManager->getSegmentInstance();
        $firstChild->setCode('first child');

        $secondChild = $this->segmentManager->getSegmentInstance();
        $secondChild->setCode('second child');

        $originalChildren = new ArrayCollection(array($firstChild, $secondChild));

        $this->entityRepository->expects($this->any())
            ->method('getChildrenByParentId')
            ->with($this->greaterThan(0))
            ->will($this->returnValue($originalChildren));

        $childrenFromManager = $this->segmentManager->getChildren(1);

        $this->assertEquals($originalChildren, $childrenFromManager);

    }

    /**
     * Test related method
     */
    public function testSearch()
    {
        $firstChild = $this->segmentManager->getSegmentInstance();
        $firstChild->setCode('first child');

        $secondChild = $this->segmentManager->getSegmentInstance();
        $secondChild->setCode('second child');

        $originalChildren = new ArrayCollection(array($firstChild, $secondChild));

        $this->entityRepository->expects($this->any())
            ->method('search')
            ->will($this->returnValue($originalChildren));

        $childrenFromManager = $this->segmentManager->search(1, array());

        $this->assertEquals($originalChildren, $childrenFromManager);
    }
}
