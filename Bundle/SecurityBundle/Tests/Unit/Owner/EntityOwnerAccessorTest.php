<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\EntityBundle\ORM\EntityClassAccessor;
use Oro\Bundle\SecurityBundle\Tests\Unit\Owner\Fixtures\OwnershipMetadataProviderStub;
use Oro\Bundle\SecurityBundle\Tests\Unit\Owner\Fixtures\Entity\TestEntity;
use Oro\Bundle\SecurityBundle\Tests\Unit\Owner\Fixtures\Entity\TestEntityWithOwnerFieldButWithoutGetOwnerMethod;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;

class EntityOwnerAccessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOwner()
    {
        $metadataProvider = new OwnershipMetadataProviderStub($this);
        $accessor = new EntityOwnerAccessor(new EntityClassAccessor(), $metadataProvider);

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
        $accessor = new EntityOwnerAccessor(new EntityClassAccessor(), new OwnershipMetadataProviderStub($this));

        $obj = new TestEntity('testId');
        $obj->setOwner('testOwner');
        $this->assertNull($accessor->getOwner($obj));
    }

    /**
     * @expectedException \Oro\Bundle\EntityBundle\Exception\InvalidEntityException
     */
    public function testGetOwnerNull()
    {
        $accessor = new EntityOwnerAccessor(new EntityClassAccessor(), new OwnershipMetadataProviderStub($this));
        $accessor->getOwner(null);
    }

    /**
     * @expectedException \Oro\Bundle\EntityBundle\Exception\InvalidEntityException
     */
    public function testGetOwnerNoGetOwnerAndNoOwnerField()
    {
        $metadataProvider = new OwnershipMetadataProviderStub($this);
        $accessor = new EntityOwnerAccessor(new EntityClassAccessor(), $metadataProvider);

        $obj = new \stdClass();
        $metadataProvider->setMetadata(get_class($obj), new OwnershipMetadata('ORGANIZATION', 'owner', 'owner_id'));

        $accessor->getOwner($obj);
    }
}
