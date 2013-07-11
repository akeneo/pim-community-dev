<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Entity;

use Oro\Bundle\TagBundle\Entity\Tag;

class TagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tag
     */
    protected $tag;

    public function setUp()
    {
        $this->tag = new Tag();
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
}
