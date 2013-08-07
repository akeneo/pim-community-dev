<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\EventListener;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\EventListener\RecordOwnerDataListener;

class RecordOwnerDataListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RecordOwnerDataListener
     */
    private $listener;

    private $container;

    private $securityContext;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->stringEndsWith('security.context'))
            ->will($this->returnValue($this->securityContext));

        $this->listener = new RecordOwnerDataListener($this->container);
    }

    public function testPrePersist()
    {
        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $user = new User();
        $businessUnit = new BusinessUnit();
        $organization = new Organization();

        $businessUnit->setOrganization($organization);
        $user->setBusinessUnit($businessUnit);

        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $listenerArguments = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $listenerArguments->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($user));

        $this->listener->prePersist($listenerArguments);

        $this->assertEquals($businessUnit, $user->getBusinessUnitOwner());
        $this->assertEquals($user, $user->getUserOwner());
        $this->assertEquals($organization, $user->getOrganizationOwner());
    }
}
