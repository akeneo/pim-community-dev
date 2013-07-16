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
    protected $mapper;
    protected $securityContext;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->manager = new TagManager(
            $this->em,
            'Oro\Bundle\TagBundle\Entity\Tag',
            'Oro\Bundle\TagBundle\Entity\Tagging',
            $this->mapper,
            $this->securityContext
        );
    }

    public function testDeleteTagging()
    {

    }
}
