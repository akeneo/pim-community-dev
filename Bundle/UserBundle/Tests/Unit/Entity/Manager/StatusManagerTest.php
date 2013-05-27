<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\UserBundle\Entity\Manager\StatusManager;
use Oro\Bundle\UserBundle\Entity\Status;

class StatusManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Entity\Manager\StatusManager
     */
    private $manager;

    private $em;
    private $um;

    private $repository;

    private $user;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->um = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\UserManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = $this->getMock(
            'Doctrine\Common\Persistence\ObjectRepository',
            array('find', 'findAll', 'findBy', 'findOneBy', 'getClassName')
        );

        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->user = $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Entity\User');

        $this->manager = new StatusManager($this->em, $this->um);
    }

    public function testGetUserStatuses()
    {
        $this->repository->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue(array()));

        $this->manager->getUserStatuses($this->user);
    }

    public function testDeleteStatus()
    {
        $status = new Status();

        $this->assertFalse($this->manager->deleteStatus($this->user, $status, true));

        $status->setUser($this->user);
        $this->user->setCurrentStatus($status);

        $this->um->expects($this->once())
            ->method('updateUser');

        $this->um->expects($this->once())
            ->method('reloadUser');

        $this->em->expects($this->once())
            ->method('remove');

        $this->em->expects($this->once())
            ->method('flush');
        $this->assertTrue($this->manager->deleteStatus($this->user, $status, true));
    }

    public function testSetCurrentStatus()
    {
        $status = new Status();

        $this->um->expects($this->once())
            ->method('updateUser');

        $this->um->expects($this->once())
            ->method('reloadUser');
        $this->manager->setCurrentStatus($this->user, $status, true);
    }
}
