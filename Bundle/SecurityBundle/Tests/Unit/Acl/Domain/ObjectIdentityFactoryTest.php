<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class ObjectIdentityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectIdentityFactory
     */
    private $factory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = new ObjectIdentityFactory($this->em);
    }

    /**
     * @dataProvider fromObjectProvider
     */
    public function testFromObjectPrefersInterfaceOverGetId($methodName)
    {
        $obj = $this->getMock('Symfony\Component\Security\Acl\Model\DomainObjectInterface');
        $obj
            ->expects($this->once())
            ->method('getObjectIdentifier')
            ->will($this->returnValue('getObjectIdentifier()'));
        $obj
            ->expects($this->never())
            ->method('getId')
            ->will($this->returnValue('getId()'));

        $id = $this->factory->{$methodName}($obj);
        $this->assertEquals('getObjectIdentifier()', $id->getIdentifier());
    }

    /**
     * @dataProvider fromObjectProvider
     */
    public function testFromObjectWithoutInterface($methodName)
    {
        $id = $this->factory->{$methodName}(new TestDomainObject());
        $this->assertEquals('getId()', $id->getIdentifier());
        $this->assertEquals(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject',
            $id->getType()
        );
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($desctiptor, $expectedId, $expectedType)
    {
        $config = $this->getMockBuilder('\Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($config));
        $config->expects($this->any())
            ->method('getEntityNamespace')
            ->with($this->equalTo('AcmeBundle'))
            ->will($this->returnValue('AcmeBundle\Entity'));

        $id = $this->factory->get($desctiptor);
        $this->assertEquals($expectedId, $id->getIdentifier());
        $this->assertEquals($expectedType, $id->getType());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIncorrectClassDescriptor()
    {
        $this->factory->get('AcmeBundle\SomeClass');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIncorrectEntityDescriptor()
    {
        $this->factory->get('AcmeBundle:SomeEntity');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIncorrectActionDescriptor()
    {
        $this->factory->get('Some Action');
    }

    public function testForEntityClass()
    {
        $config = $this->getMockBuilder('\Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getConfiguration')
            ->will($this->returnValue($config));
        $config->expects($this->once())
            ->method('getEntityNamespace')
            ->with($this->equalTo('AcmeBundle'))
            ->will($this->returnValue('AcmeBundle\Entity'));

        $id = $this->factory->forEntityClass('AcmeBundle:SomeEntity');
        $this->assertEquals('class', $id->getIdentifier());
        $this->assertEquals('AcmeBundle\Entity\SomeEntity', $id->getType());

        // Test that the factory use the local cache - no extra call of EntityManager must be performed
        $this->factory->forEntityClass('AcmeBundle:SomeEntity');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testForEntityClassWithInvalidEntityName()
    {
        $this->factory->forEntityClass('AcmeBundle:Entity:SomeEntity');
    }

    public function testForEntityClassWithFullClassName()
    {
        $id = $this->factory->forEntityClass('AcmeBundle\Entity\SomeEntity');
        $this->assertEquals('class', $id->getIdentifier());
        $this->assertEquals('AcmeBundle\Entity\SomeEntity', $id->getType());
    }

    public function testForClass()
    {
        $id = $this->factory->forClass('AcmeBundle\SomeClass');
        $this->assertEquals('class', $id->getIdentifier());
        $this->assertEquals('AcmeBundle\SomeClass', $id->getType());
    }

    public function testForAction()
    {
        $id = $this->factory->forAction('Some Action');
        $this->assertEquals('action', $id->getIdentifier());
        $this->assertEquals('Some Action', $id->getType());
    }

    public static function fromObjectProvider()
    {
        return array(
            'fromDomainObject' => array('fromDomainObject'),
            'fromEntityObject' => array('fromEntityObject'),
        );
    }

    public static function getProvider()
    {
        return array(
            'Class' => array('Class:AcmeBundle\SomeClass', 'class', 'AcmeBundle\SomeClass'),
            'CLASS' => array('CLASS:AcmeBundle\SomeClass', 'class', 'AcmeBundle\SomeClass'),
            'Entity' => array('Entity:AcmeBundle:SomeEntity', 'class', 'AcmeBundle\Entity\SomeEntity'),
            'ENTITY' => array('ENTITY:AcmeBundle:SomeEntity', 'class', 'AcmeBundle\Entity\SomeEntity'),
            'Entity (class name)' => array(
                'Entity:AcmeBundle\Entity\SomeEntity',
                'class',
                'AcmeBundle\Entity\SomeEntity'
            ),
            'Action' => array('Action:Some Action', 'action', 'Some Action'),
            'ACTION' => array('ACTION:Some Action', 'action', 'Some Action'),
        );
    }
}
