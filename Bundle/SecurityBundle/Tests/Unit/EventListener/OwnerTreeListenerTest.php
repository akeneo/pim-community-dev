<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\EventListener;

use Oro\Bundle\SecurityBundle\EventListener\OwnerTreeListener;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OwnerTreeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testOnFlush($inserts, $updates, $deletions, $isExpectedCache)
    {
        $treeProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($treeProvider));
        $args = $this->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($em));
        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));
        $uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue($inserts));
        $uow->expects($this->any())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue($updates));
        $uow->expects($this->any())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue($deletions));
        if ($isExpectedCache) {
            $treeProvider->expects($this->once())
                ->method('clear');
        } else {
            $treeProvider->expects($this->never())
                ->method('clear');
        }

        $treeListener = new OwnerTreeListener($serviceLink);
        $treeListener->onFlush($args);
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [
                [new User()],
                [],
                [new \stdClass()],
                true
            ],
            [
                [new User()],
                [new BusinessUnit()],
                [new \stdClass()],
                true
            ],
            [
                [],
                [new User()],
                [],
                true
            ],
            [
                [],
                [new \stdClass()],
                [new Organization()],
                true
            ],
            [
                [new \stdClass()],
                [],
                [],
                false
            ],
            [
                [],
                [],
                [],
                false
            ]
        ];
    }
}
