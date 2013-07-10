<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Twig;

use Oro\Bundle\TagBundle\Twig\TagExtension;

class TagExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));

        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->extension = new TagExtension($this->manager, $this->router, $this->securityContext);
    }

    public function testName()
    {
        $this->assertEquals('oro_tag', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $this->assertArrayHasKey('oro_tag_get_list', $this->extension->getFunctions());
    }

    public function testGet()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\Taggable');
        $tag1 = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tag2 = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $tagging = $this->getMock('Oro\Bundle\TagBundle\Entity\Tagging');

        $tag1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test name 1'));
        $tag1->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(1));
        $tag1->expects($this->once())
            ->method('getTagging')
            ->will($this->returnValue(array($tagging)));

        $tag2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test name 2'));
        $tag2->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(2));
        $tag2->expects($this->once())
            ->method('getTagging')
            ->will($this->returnValue(array($tagging)));

        $userMock = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $tagging->expects($this->exactly(2))
            ->method('getUser')
            ->will($this->returnValue($userMock));

        $userMock->expects($this->at(0))
            ->method('getId')
            ->will($this->returnValue('uniqueId'));
        $userMock->expects($this->at(1))
            ->method('getId')
            ->will($this->returnValue('uniqueId2'));

        $this->user->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue('uniqueId'));

        $this->router->expects($this->exactly(2))
            ->method('generate');

        $this->manager->expects($this->once())
            ->method('loadTagging');

        $tags = array(
            $tag1, $tag2
        );

        $entity->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue($tags));

        $result = $this->extension->get($entity);

        $this->assertCount(2, $result);

        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('owner', $result[0]);

        $this->assertArrayNotHasKey('owner', $result[1]);

    }
}
