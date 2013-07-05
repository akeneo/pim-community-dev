<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Entity;

use Oro\Bundle\TagBundle\Entity\TagManager;

class TagManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagManager
     */
    protected $manager;

    protected $em;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new TagManager($this->em, 'Oro\Bundle\TagBundle\Entity\Tag', 'Oro\Bundle\TagBundle\Entity\Tagging');
    }

    public function testDeleteTagging()
    {

    }
}
