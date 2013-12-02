<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Tests\Unit\Fixtures\Taggable;

class TagManagerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TAG_NAME     = 'testName';
    const TEST_NEW_TAG_NAME = 'testAnotherName';
    const TEST_TAG_ID       = 3333;

    const TEST_ENTITY_NAME  = 'test name';
    const TEST_RECORD_ID    = 1;
    const TEST_CREATED_ID   = 22;

    const TEST_USER_ID      = 'someID';

    /** @var TagManager */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mapper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $router;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $user;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()->getMock();

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()->getMock();

        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()->getMock();

        $this->user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $this->user->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TEST_USER_ID));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->manager = new TagManager(
            $this->em,
            'Oro\Bundle\TagBundle\Entity\Tag',
            'Oro\Bundle\TagBundle\Entity\Tagging',
            $this->mapper,
            $this->securityContext,
            $this->securityFacade,
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
    public function testDeleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy, $expectedCallArg)
    {
        $repo = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\Repository\TagRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('deleteTaggingByParams')
            ->with($expectedCallArg, $entityName, $recordId, $createdBy);

        $this->em->expects($this->once())->method('getRepository')
            ->will($this->returnValue($repo));

        $this->manager->deleteTaggingByParams($tagIds, $entityName, $recordId, $createdBy);
    }

    /**
     * @return array
     */
    public function tagIdsProvider()
    {
        $tag = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tag->expects($this->once())->method('getId')
            ->will($this->returnValue(self::TEST_TAG_ID));

        return array(
            'null value should pass as array' => array(
                'tagIds'           => null,
                'entityName'       => self::TEST_ENTITY_NAME,
                'recordId'         => self::TEST_RECORD_ID,
                'createdBy'        => self::TEST_CREATED_ID,
                'expectedCallArg'  => array()
            ),
            'some ids data ' => array(
                'tagIds'           => array(self::TEST_TAG_ID),
                'entityName'       => self::TEST_ENTITY_NAME,
                'recordId'         => self::TEST_RECORD_ID,
                'createdBy'        => self::TEST_CREATED_ID,
                'expectedCallArg'  => array(self::TEST_TAG_ID)
            ),
            'some array collection' => array(
                'tagIds'            => new ArrayCollection(array($tag)),
                'entityName'        => self::TEST_ENTITY_NAME,
                'recordId'          => self::TEST_RECORD_ID,
                'createdBy'         => self::TEST_CREATED_ID,
                'expectedCallArg'   => array(self::TEST_TAG_ID)

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

    public function testCompareCallback()
    {
        $tag = new Tag('testName');
        $tagToCompare = new Tag('testName');
        $tagToCompare2 = new Tag('notTheSameName');

        $callback = $this->manager->compareCallback($tag);

        $this->assertTrue($callback(1, $tagToCompare));
        $this->assertFalse($callback(1, $tagToCompare2));
    }

    public function testGetPreparedArrayFromDb()
    {
        $resource = new Taggable(array('id' => 1));
        $tagging = $this->getMock('Oro\Bundle\TagBundle\Entity\Tagging');

        $tag1 = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tag1->expects($this->once())->method('getName')
            ->will($this->returnValue('test name 1'));
        $tag1->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $tag1->expects($this->exactly(1))->method('getTagging')
            ->will($this->returnValue(new ArrayCollection(array($tagging))));

        $tag2 = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tag2->expects($this->once())->method('getName')
            ->will($this->returnValue('test name 2'));
        $tag2->expects($this->any())->method('getId')
            ->will($this->returnValue(2));
        $tag2->expects($this->exactly(1))->method('getTagging')
            ->will($this->returnValue(new ArrayCollection(array($tagging))));

        $userMock = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $tagging->expects($this->exactly(2))
            ->method('getCreatedBy')->will($this->returnValue($userMock));
        $tagging->expects($this->any())
            ->method('getEntityName')->will($this->returnValue(get_class($resource)));
        $tagging->expects($this->any())
            ->method('getRecordId')->will($this->returnValue(1));

        $userMock->expects($this->at(0))
            ->method('getId')->will($this->returnValue(self::TEST_USER_ID));
        $userMock->expects($this->at(1))->method('getId')
            ->will($this->returnValue('uniqueId2'));

        $this->user->expects($this->exactly(2))->method('getId')
            ->will($this->returnValue(self::TEST_USER_ID));

        $this->router->expects($this->exactly(2))
            ->method('generate');

        $repo = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\Repository\TagRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getTagging')->with($resource, null, false)
            ->will(
                $this->returnValue(
                    array($tag1, $tag2)
                )
            );

        $this->em->expects($this->once())->method('getRepository')->with('Oro\Bundle\TagBundle\Entity\Tag')
            ->will($this->returnValue($repo));

        $result = $this->manager->getPreparedArray($resource);

        $this->assertCount(2, $result);

        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('owner', $result[0]);

        $this->assertFalse($result[1]['owner']);
        $this->assertTrue($result[0]['owner']);
    }

    public function testGetPreparedArrayFromArray()
    {
        $resource = new Taggable(array('id' => 1));

        $this->user->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(self::TEST_USER_ID));

        $this->router->expects($this->once())
            ->method('generate');

        $repo = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\Repository\TagRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getTagging');

        $this->manager->getPreparedArray($resource, $this->tagForPreparing());
    }

    protected function tagForPreparing()
    {
        $tag1 = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tag2 = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tagging = $this->getMock('Oro\Bundle\TagBundle\Entity\Tagging');

        $tag1->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('test name 1'));
        $tag1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $tag1->expects($this->exactly(1))
            ->method('getTagging')
            ->will($this->returnValue(new ArrayCollection(array($tagging))));

        $tag2->expects($this->any())->method('getId')
            ->will($this->returnValue(2));
        $tag2->expects($this->once())->method('getName')
            ->will($this->returnValue('test name 2'));
        $tag2->expects($this->exactly(1))->method('getTagging')
            ->will($this->returnValue(new ArrayCollection(array($tagging))));

        $userMock = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $tagging->expects($this->exactly(2))
            ->method('getCreatedBy')->will($this->returnValue($userMock));
        $tagging->expects($this->any())
            ->method('getEntityName')->will($this->returnValue('Oro\Bundle\TagBundle\Tests\Unit\Fixtures\Taggable'));
        $tagging->expects($this->any())
            ->method('getRecordId')->will($this->returnValue(1));

        $userMock->expects($this->at(0))
            ->method('getId')->will($this->returnValue(self::TEST_USER_ID));
        $userMock->expects($this->at(1))
            ->method('getId')->will($this->returnValue('uniqueId2'));

        return new ArrayCollection(array($tag1, $tag2));
    }
}
