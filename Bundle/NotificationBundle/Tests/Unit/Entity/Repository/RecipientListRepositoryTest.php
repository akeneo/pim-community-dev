<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\NotificationBundle\Entity\Repository\RecipientListRepository;

class RecipientListRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAME = 'OroUserBundle:User';

    /**
     * @var RecipientListRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $this->repository = new RecipientListRepository($this->entityManager, new ClassMetadata(self::ENTITY_NAME));
    }

    protected function tearDown()
    {
        unset($this->repository);
        unset($this->entityManager);
    }

    public function testGetRecipientEmails()
    {
        $userMock = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $userMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('a@a.com'));

        $groupMock = $this->getMock('Oro\Bundle\UserBundle\Entity\Group');
        $groupMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));

        $users = new ArrayCollection(array($userMock));
        $groups = new ArrayCollection(array($groupMock));

        $recipientList = $this->getMock('Oro\Bundle\NotificationBundle\Entity\RecipientList');
        $recipientList->expects($this->once())
            ->method('getUsers')
            ->will($this->returnValue($users));
        $recipientList->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue($groups));

        $recipientList->expects($this->once())
            ->method('getOwner')
            ->will($this->returnValue(true));

        $recipientList->expects($this->exactly(2))
            ->method('getEmail')
            ->will($this->returnValue('a@a.com'));

        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $user->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('b@b.com'));

        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $entity->expects($this->once())
            ->method('getCreatedBy')
            ->will($this->returnValue($user));

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getResult')
            ->will($this->returnValue(array(array('email' => 'b@b.com'))));

        $entityAlias = 'u';

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'from', 'getQuery', 'leftJoin', 'where', 'setParameter'))
            ->getMock();
        $queryBuilder->expects($this->once())->method('select')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('from')->with(self::ENTITY_NAME, $entityAlias)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('getQuery')
            ->will($this->returnValue($query));
        $queryBuilder->expects($this->once())->method('leftJoin')->with('u.groups', 'groups')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('where')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())->method('setParameter')
            ->will($this->returnSelf());

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));


        $emails = $this->repository->getRecipientEmails($recipientList, $entity);
        $this->assertCount(2, $emails);
    }
}
