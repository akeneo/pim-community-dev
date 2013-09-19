<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntityImplementsDomainObjectInterface;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;

class ObjectIdentityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectIdentityFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ObjectIdentityFactory(
            TestHelper::get($this)->createAclExtensionSelector()
        );
    }

    public function testRoot()
    {
        $id = $this->factory->root('entity');
        $this->assertEquals('entity', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());

        $id = $this->factory->root('Entity');
        $this->assertEquals('entity', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());

        $id = $this->factory->root($this->factory->get('Entity: Test:TestEntity'));
        $this->assertEquals('entity', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());

        $id = $this->factory->root($this->factory->get(new TestEntity(123)));
        $this->assertEquals('entity', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());

        $id = $this->factory->root('action');
        $this->assertEquals('action', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());

        $id = $this->factory->root('Action');
        $this->assertEquals('action', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());

        $id = $this->factory->root($this->factory->get('Action: Some Action'));
        $this->assertEquals('action', $id->getIdentifier());
        $this->assertEquals(ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $id->getType());
    }

    public function testFromDomainObjectPrefersInterfaceOverGetId()
    {
        $obj = new TestEntityImplementsDomainObjectInterface('getObjectIdentifier()');
        $id = $this->factory->get($obj);
        $this->assertEquals('getObjectIdentifier()', $id->getIdentifier());
        $this->assertEquals(get_class($obj), $id->getType());
    }

    public function testFromDomainObjectWithoutDomainObjectInterface()
    {
        $obj = new TestEntity('getId()');
        $id = $this->factory->get($obj);
        $this->assertEquals('getId()', $id->getIdentifier());
        $this->assertEquals(get_class($obj), $id->getType());
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
        $this->factory->get(new TestEntityImplementsDomainObjectInterface());
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($descriptor, $expectedId, $expectedType)
    {
        $id = $this->factory->get($descriptor);
        $this->assertEquals($expectedType, $id->getType());
        $this->assertEquals($expectedId, $id->getIdentifier());
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

    public function testFromEntityAclAnnotation()
    {
        $obj = new AclAnnotation(array('id' => 'test', 'type'=> 'entity', 'class' => 'Acme\SomeEntity'));
        $id = $this->factory->get($obj);
        $this->assertEquals('entity', $id->getIdentifier());
        $this->assertEquals('Acme\SomeEntity', $id->getType());
    }

    public function testFromActionAclAnnotation()
    {
        $obj = new AclAnnotation(array('id' => 'test_action', 'type'=> 'action'));
        $id = $this->factory->get($obj);
        $this->assertEquals('action', $id->getIdentifier());
        $this->assertEquals('test_action', $id->getType());
    }

    public static function getProvider()
    {
        return array(
            'Entity' => array(
                'Entity:Test:TestEntity',
                'entity',
                'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity'
            ),
            'Entity (whitespace)' => array(
                'Entity: Test:TestEntity',
                'entity',
                'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity'
            ),
            'ENTITY' => array(
                'ENTITY:Test:TestEntity',
                'entity',
                'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity'
            ),
            'Entity (class name)' => array(
                'Entity: Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity',
                'entity',
                'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity'
            ),
            'Action' => array('Action:Some Action', 'action', 'Some Action'),
            'Action (whitespace)' => array('Action: Some Action', 'action', 'Some Action'),
            'ACTION' => array('ACTION:Some Action', 'action', 'Some Action'),
        );
    }
}
