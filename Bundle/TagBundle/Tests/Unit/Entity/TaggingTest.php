<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Entity;

use Oro\Bundle\TagBundle\Entity\Tagging;

class TaggingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tagging
     */
    protected $tagging;

    public function setUp()
    {
        $this->tagging = new Tagging();
    }

    public function testSetGetUserMethods()
    {
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $this->tagging->setCreatedBy($user);
        $this->assertEquals($user, $this->tagging->getCreatedBy());
    }

    public function testSetGetTagMethods()
    {
        $tag = $this->getMock('Oro\Bundle\TagBundle\Entity\Tag');
        $this->tagging->setTag($tag);

        $this->assertEquals($tag, $this->tagging->getTag());

        // test pass tag through constructor
        $tagging = new Tagging($tag);

        $this->assertEquals($tag, $tagging->getTag());
    }

    public function testSetGetResourceMethods()
    {
        $resource = $this->getMockForAbstractClass('Oro\Bundle\TagBundle\Entity\Taggable');
        $resource->expects($this->exactly(2))
            ->method('getTaggableId')
            ->will($this->returnValue(1));

        $this->tagging->setResource($resource);

        $this->assertEquals(1, $this->tagging->getRecordId());
        $this->assertEquals(get_class($resource), $this->tagging->getEntityName());

        // test pass resource through constructor
        $tagging = new Tagging(null, $resource);

        $this->assertEquals(1, $tagging->getRecordId());
        $this->assertEquals(get_class($resource), $tagging->getEntityName());
    }

    public function testDateTimeMethods()
    {
        $timeCreated = new \DateTime('now');
        $this->tagging->setCreatedAt($timeCreated);
        $this->assertEquals($timeCreated, $this->tagging->getCreatedAt());
    }
}
