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

        $this->tagging->setUser($user);
        $this->assertEquals($user, $this->tagging->getUser());
    }
}
