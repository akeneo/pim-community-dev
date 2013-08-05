<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Entity;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

class TagManagerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TAG_NAME     = 'testName';
    const TEST_NEW_TAG_NAME = 'testAnotherName';
    const TEST_TAG_ID       = 3333;

    const TEST_ENTITY_NAME  = 'test name';
    const TEST_RECORD_ID    = 1;
    const TEST_CREATED_ID   = 22;

    /** @var TagManager */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mapper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aclManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $router;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()->getMock();

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->aclManager = $this->getMockBuilder('Oro\Bundle\UserBundle\Acl\Manager')
            ->disableOriginalConstructor()->getMock();

        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()->getMock();

        $this->manager = new TagManager(
            $this->em,
            'Oro\Bundle\TagBundle\Entity\Tag',
            'Oro\Bundle\TagBundle\Entity\Tagging',
            $this->mapper,
            $this->securityContext,
            $this->aclManager,
            $this->router
        );
    }

    public function testAddTags()
    {
        $testTags = array(new Tag(self::TEST_TAG_NAME));

        $collection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $collection->expects($this->once())->method('add');

        $resource = $this->getMockForAbstractClass('Oro\Bundle\TagBundle\Entity\Taggable');
        $resource->expects($this->once())->method('getTags')
            ->will($this->returnValue($collection));

        $this->manager->addTags($testTags, $resource);
    }

    /**
     * @dataProvider getTagNames
     * @param array $names
     * @param int|bool $shouldWorkWithDB
     * @param int $resultCount
     * @param array $tagsFromDB
     */
    public function testLoadOrCreateTags($names, $shouldWorkWithDB, $resultCount, array $tagsFromDB)
    {
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()->getMock();
        $this->em->expects($this->exactly((int) $shouldWorkWithDB))->method('getRepository')
            ->will($this->returnValue($repo));

        $repo->expects($this->exactly((int) $shouldWorkWithDB))->method('findBy')
            ->will($this->returnValue($tagsFromDB));

        $result = $this->manager->loadOrCreateTags($names);

        $this->assertCount($resultCount, $result);
        if ($shouldWorkWithDB) {
            $this->assertContainsOnlyInstancesOf('Oro\Bundle\TagBundle\Entity\Tag', $result);
        }

    }

    /**
     * @return array
     */
    public function getTagNames()
    {
        return array(
            'with empty tag name will return empty array' => array(
                'names' => array(),
                'shouldWorkWithDB' => false,
                'resultCount' => 0,
                array()
            ),
            'with 1 tag from DB and 1 new tag' => array(
                'names' => array(self::TEST_TAG_NAME, self::TEST_NEW_TAG_NAME),
                'shouldWorkWithDB' => true,
                'resultCount' => 2,
                array(new Tag(self::TEST_TAG_NAME))
            )
        );
    }

    /**
     * @dataProvider tagIdsProvider
     */
    public function testDeleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy)
    {
        $repo = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\Repository\TagRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('deleteTaggingByParams')
            ->with(is_array($tagIds) ? $tagIds : array(), $entityName, $recordId, $createdBy);

        $this->em->expects($this->once())->method('getRepository')
            ->will($this->returnValue($repo));

        $this->manager->deleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy);
    }

    /**
     * @return array
     */
    public function tagIdsProvider()
    {
        return array(
            'null value should pass as array' => array(
                'tagIds'     => null,
                'entityName' => self::TEST_ENTITY_NAME,
                'recordId'   => self::TEST_RECORD_ID,
                'createdBy'  => self::TEST_CREATED_ID
            ),
            'some ids data ' => array(
                'tagIds'     => array(self::TEST_TAG_ID),
                'entityName' => self::TEST_ENTITY_NAME,
                'recordId'   => self::TEST_RECORD_ID,
                'createdBy'  => self::TEST_CREATED_ID
            )
        );
    }

    public function testLoadTagging()
    {
        $collection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $collection->expects($this->once())->method('add');

        $resource = $this->getMockForAbstractClass('Oro\Bundle\TagBundle\Entity\Taggable');
        $resource->expects($this->once())->method('getTags')
            ->will($this->returnValue($collection));

        $repo = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\Repository\TagRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getTagging')
            ->with($resource, null, false)
            ->will(
                $this->returnValue(
                    array(
                        new Tag(self::TEST_TAG_NAME)
                    )
                )
            );

        $this->em->expects($this->once())->method('getRepository')->with('Oro\Bundle\TagBundle\Entity\Tag')
            ->will($this->returnValue($repo));

        $this->manager->loadTagging($resource);
    }
}
