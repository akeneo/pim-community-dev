<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\UserBundle\Entity\Manager\GroupManager;

class GroupManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Entity\Manager\GroupManager
     */
    private $manager;

    private $em;

    private $repository;

    private $group;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = $this->getMock(
            'Doctrine\Common\Persistence\ObjectRepository',
            array('find', 'findAll', 'findBy', 'findOneBy', 'getClassName', 'getUserQueryBuilder')
        );

        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->manager = new GroupManager($this->em);
        $this->group = $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Entity\Group');
    }

    public function testGetUserQueryBuilder()
    {
        $this->repository->expects($this->once())
            ->method('getUserQueryBuilder')
            ->with($this->group)
            ->will($this->returnValue(array()));

        $this->manager->getUserQueryBuilder($this->group);
    }
}
