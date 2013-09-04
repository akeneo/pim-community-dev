<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\UserBundle\Entity\User;

class TagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new Tag();

        $this->assertEquals(null, $this->tag->getId());
    }

    public function testSetGetNameMethods()
    {
        $this->tag->setName('test');
        $this->assertEquals('test', $this->tag->getName());

        $tag = new Tag('test 2');
        $this->assertEquals('test 2', $tag->getName());
        $this->assertEquals('test 2', $tag->__toString());
    }

    public function testDateTimeMethods()
    {
        $timeCreated = new \DateTime('now');
        $timeUpdated = new \DateTime('now');

        $this->tag->setCreatedAt($timeCreated);
        $this->tag->setUpdatedAt($timeUpdated);

        $this->assertEquals($timeCreated, $this->tag->getCreatedAt());
        $this->assertEquals($timeUpdated, $this->tag->getUpdatedAt());
    }

    public function testAuthorAndUpdaterStoring()
    {
        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $user1 = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $this->tag->setCreatedBy($user);
        $this->assertEquals($user, $this->tag->getCreatedBy());

        $this->tag->setUpdatedBy($user1);
        $this->assertEquals($user1, $this->tag->getUpdatedBy());
    }

    public function testUpdatedTime()
    {
        $this->assertNotEquals(null, $this->tag->getUpdatedAt());
        $oldUpdatedTime = $this->tag->getUpdatedAt();

        sleep(1);
        $this->tag->doUpdate();
        $this->assertInstanceOf('\DateTime', $this->tag->getUpdatedAt());
        $this->assertNotEquals($oldUpdatedTime, $this->tag->getUpdatedAt());
    }

    public function testGetTagging()
    {
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->tag->getTagging());
    }

    public function testOwners()
    {
        $entity = $this->tag;
        $user = new User();

        $this->assertEmpty($entity->getOwner());

        $entity->setOwner($user);

        $this->assertEquals($user, $entity->getOwner());
    }
}
