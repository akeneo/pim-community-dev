<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\EventListener;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\EventListener\RecordOwnerDataListener;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

use Oro\Bundle\UserBundle\Tests\Unit\Fixture\Entity;

class RecordOwnerDataListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RecordOwnerDataListener
     */
    private $listener;

    /**
     * @var Entity
     */
    private $entity;

    private $container;

    private $configProvider;

    private $securityContext;

    private $config;

    private $user;
    private $businessUnit;
    private $organization;

    private $listenerArguments;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->stringEndsWith('security.context'))
            ->will($this->returnValue($this->securityContext));

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = new User();
        $this->businessUnit = new BusinessUnit();
        $this->organization = new Organization();
        $businessUnits = new ArrayCollection(array($this->businessUnit));

        $this->businessUnit->setOrganization($this->organization);
        $this->user->setBusinessUnits($businessUnits);

        $this->entity = new Entity();

        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->will($this->returnValue(true));

        $this->config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\EntityConfig')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($this->config));

        $this->listenerArguments = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listenerArguments->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->entity));

        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($this->user));

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));


        $this->listener = new RecordOwnerDataListener($this->container, $this->configProvider);
    }

    public function testPrePersistUser()
    {
        $this->config->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('owner_type' => OwnershipType::OWNERSHIP_TYPE_USER)));

        $this->listener->prePersist($this->listenerArguments);
        $this->assertEquals($this->user, $this->entity->getOwner());
    }

    public function testPrePersistBusinessUnit()
    {
        $this->config->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('owner_type' => OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT)));

        $this->listener->prePersist($this->listenerArguments);
        $this->assertEquals($this->businessUnit, $this->entity->getOwner());
    }

    public function testPrePersistOrganization()
    {
        $this->config->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(array('owner_type' => OwnershipType::OWNERSHIP_TYPE_ORGANIZATION)));

        $this->listener->prePersist($this->listenerArguments);
        $this->assertEquals($this->organization, $this->entity->getOwner());
    }
}
