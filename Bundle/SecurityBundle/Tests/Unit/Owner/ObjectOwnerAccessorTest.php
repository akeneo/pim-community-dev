<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntityWithOwnerFieldButWithoutGetOwnerMethod;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

class ObjectOwnerAccessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOwner()
    {
        $metadataProvider = new OwnershipMetadataProvider();
        $accessor = new ObjectOwnerAccessor(new ObjectClassAccessor(), $metadataProvider);

        $obj1 = new TestEntity('testId1');
        $obj1->setOwner('testOwner1');
        $metadataProvider->setMetadata(get_class($obj1), new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id'));
        $this->assertEquals('testOwner1', $accessor->getOwner($obj1));

        $obj2 = new TestEntityWithOwnerFieldButWithoutGetOwnerMethod('testOwner2');
        $metadataProvider->setMetadata(get_class($obj2), new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id'));
        $this->assertEquals('testOwner2', $accessor->getOwner($obj2));
    }

    public function testGetOwnerNoMetadata()
    {
        $accessor = new ObjectOwnerAccessor(new ObjectClassAccessor(), new OwnershipMetadataProvider());

        $obj = new TestEntity('testId');
        $obj->setOwner('testOwner');
        $this->assertNull($accessor->getOwner($obj));
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetOwnerNull()
    {
        $accessor = new ObjectOwnerAccessor(new ObjectClassAccessor(), new OwnershipMetadataProvider());
        $accessor->getOwner(null);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException
     */
    public function testGetOwnerNoGetOwnerAndNoOwnerField()
    {
        $metadataProvider = new OwnershipMetadataProvider();
        $accessor = new ObjectOwnerAccessor(new ObjectClassAccessor(), $metadataProvider);

        $obj = new \stdClass();
        $metadataProvider->setMetadata(get_class($obj), new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id'));

        $accessor->getOwner($obj);
    }
}
