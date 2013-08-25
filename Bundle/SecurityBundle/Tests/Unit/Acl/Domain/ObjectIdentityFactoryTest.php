<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;

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
        $this->factory = new ObjectIdentityFactory(
            TestHelper::createAclExtensionSelector($this->em)
        );
    }

    public function testRoot()
    {
        $id = $this->factory->root();
        $this->assertEquals('root', $id->getIdentifier());
        $this->assertEquals('Root', $id->getType());
    }

    public function testFromDomainObjectPrefersInterfaceOverGetId()
    {
        $obj = $this->getMock('Symfony\Component\Security\Acl\Model\DomainObjectInterface');
        $obj->expects($this->once())
            ->method('getObjectIdentifier')
            ->will($this->returnValue('getObjectIdentifier()'));
        $obj->expects($this->never())
            ->method('getId')
            ->will($this->returnValue('getId()'));

        $id = $this->factory->get($obj);
        $this->assertEquals('getObjectIdentifier()', $id->getIdentifier());
    }

    public function testFromDomainObjectWithoutInterface()
    {
        $id = $this->factory->get(new TestDomainObject());
        $this->assertEquals('getId()', $id->getIdentifier());
        $this->assertEquals(
            'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject',
            $id->getType()
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testFromDomainObjectNull()
    {
        $this->factory->get(null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetShouldCatchInvalidArgumentException()
    {
        $obj = $this->getMock('Symfony\Component\Security\Acl\Model\DomainObjectInterface');
        $obj->expects($this->once())
            ->method('getObjectIdentifier')
            ->will($this->throwException(new \InvalidArgumentException()));

        $this->factory->get($obj);
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
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetIncorrectClassDescriptor()
    {
        $this->factory->get('AcmeBundle\SomeClass');
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetIncorrectEntityDescriptor()
    {
        $this->factory->get('AcmeBundle:SomeEntity');
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetWithInvalidEntityName()
    {
        $this->factory->get('entity:AcmeBundle:Entity:SomeEntity');
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetIncorrectActionDescriptor()
    {
        $this->factory->get('Some Action');
    }

    public function testGetShouldUseLocalCache()
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

        $id = $this->factory->get('entity:AcmeBundle:SomeEntity');
        $this->assertEquals('class', $id->getIdentifier());
        $this->assertEquals('AcmeBundle\Entity\SomeEntity', $id->getType());

        // Test that the factory use the local cache - no extra call of EntityManager must be performed
        $this->factory->get('entity:AcmeBundle:SomeEntity');
    }

    public static function getProvider()
    {
        return array(
            'Class' => array('Class:AcmeBundle\SomeClass', 'class', 'AcmeBundle\SomeClass'),
            'Class (whitespace)' => array('Class: AcmeBundle\SomeClass', 'class', 'AcmeBundle\SomeClass'),
            'CLASS' => array('CLASS:AcmeBundle\SomeClass', 'class', 'AcmeBundle\SomeClass'),
            'Entity' => array('Entity:AcmeBundle:SomeEntity', 'class', 'AcmeBundle\Entity\SomeEntity'),
            'Entity (whitespace)' => array('Entity: AcmeBundle:SomeEntity', 'class', 'AcmeBundle\Entity\SomeEntity'),
            'ENTITY' => array('ENTITY:AcmeBundle:SomeEntity', 'class', 'AcmeBundle\Entity\SomeEntity'),
            'Entity (class name)' => array(
                'Entity: AcmeBundle\Entity\SomeEntity',
                'class',
                'AcmeBundle\Entity\SomeEntity'
            ),
            'Action' => array('Action:Some Action', 'action', 'Some Action'),
            'Action (whitespace)' => array('Action: Some Action', 'action', 'Some Action'),
            'ACTION' => array('ACTION:Some Action', 'action', 'Some Action'),
            'Aspect' => array('Aspect:Some Aspect', 'aspect', 'Some Aspect'),
            'Aspect (whitespace)' => array('Aspect: Some Aspect', 'aspect', 'Some Aspect'),
            'ASPECT' => array('ASPECT:Some Aspect', 'aspect', 'Some Aspect'),
        );
    }
}
